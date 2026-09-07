<?php

namespace Tests\Feature;

use App\Enums\LeadStage;
use App\Enums\LeadTemperature;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadsCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected LeadSource $source;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'admin@sbl.test',
            'name' => 'Admin User',
        ]);

        $role = \App\Models\Role::firstOrCreate(['slug' => 'super-admin'], ['name' => 'Super Admin', 'is_system' => true]);
        $this->user->roles()->attach($role);

        $this->source = LeadSource::create([
            'name' => 'Facebook Page',
            'is_active' => true,
            'order' => 1,
        ]);
    }

    public function test_user_can_view_leads_index_table_and_kanban(): void
    {
        $lead = Lead::create([
            'name' => 'Kalam Hossain',
            'mobile' => '01711001122',
            'lead_source_id' => $this->source->id,
            'stage' => LeadStage::NEW,
            'temperature' => LeadTemperature::WARM,
            'score' => 45,
            'owner_user_id' => $this->user->id,
        ]);

        // Table view
        $response = $this->actingAs($this->user)->get(route('leads.index'));
        $response->assertStatus(200);
        $response->assertSee('Kalam Hossain');
        $response->assertSee('Edit');
        $response->assertSee('Delete');

        // Kanban view
        $kanbanResponse = $this->actingAs($this->user)->get(route('leads.index', ['view' => 'kanban']));
        $kanbanResponse->assertStatus(200);
        $kanbanResponse->assertSee('Kalam Hossain');
    }

    public function test_user_can_view_create_lead_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('leads.create'));
        $response->assertStatus(200);
        $response->assertSee('Add New Lead');
        $response->assertSee('Invest');
        $response->assertSee('Affiliate and Networking');
        $response->assertDontSee('Quick Tag (Section 5)');
        $response->assertDontSee('P1: Product');
    }

    public function test_user_can_store_new_lead(): void
    {
        $response = $this->actingAs($this->user)->post(route('leads.store'), [
            'name' => 'Rana Chowdhury',
            'mobile' => '01811223344',
            'whatsapp' => '01811223344',
            'lead_source_id' => $this->source->id,
            'stage' => LeadStage::QUALIFIED->value,
            'location' => 'Dhaka, Mirpur',
            'profession_or_business' => 'Textile Merchandiser',
            'notes' => 'Looking for side business opportunity.',
        ]);

        $lead = Lead::where('mobile', '01811223344')->first();
        $this->assertNotNull($lead);
        $this->assertEquals('Rana Chowdhury', $lead->name);

        $response->assertRedirect(route('leads.show', $lead->id));
    }

    public function test_user_can_view_lead_details(): void
    {
        $lead = Lead::create([
            'name' => 'Farhana Akter',
            'mobile' => '01911223344',
            'lead_source_id' => $this->source->id,
            'stage' => LeadStage::INTERESTED,
            'temperature' => LeadTemperature::HOT,
            'score' => 75,
            'owner_user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('leads.show', $lead->id));
        $response->assertStatus(200);
        $response->assertSee('Farhana Akter');
        $response->assertSee('Edit Lead');
        $response->assertSee('Delete Lead');
    }

    public function test_user_can_view_lead_edit_form(): void
    {
        $lead = Lead::create([
            'name' => 'Shakil Ahmed',
            'mobile' => '01511223344',
            'lead_source_id' => $this->source->id,
            'stage' => LeadStage::NEW,
            'temperature' => LeadTemperature::COLD,
            'score' => 20,
            'owner_user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('leads.edit', $lead->id));
        $response->assertStatus(200);
        $response->assertSee('Edit Lead Record');
        $response->assertSee('Delete Lead');
        $response->assertSee('Shakil Ahmed');
    }

    public function test_user_can_update_lead(): void
    {
        $lead = Lead::create([
            'name' => 'Shakil Ahmed',
            'mobile' => '01511223344',
            'lead_source_id' => $this->source->id,
            'stage' => LeadStage::NEW,
            'temperature' => LeadTemperature::COLD,
            'score' => 20,
            'owner_user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->put(route('leads.update', $lead->id), [
            'name' => 'Shakil Ahmed Updated',
            'mobile' => '01511223344',
            'lead_source_id' => $this->source->id,
            'stage' => LeadStage::INTERESTED->value,
            'location' => 'Chattogram',
            'notes' => 'Confirmed interest in affiliate and dropshipping.',
        ]);

        $response->assertRedirect(route('leads.show', $lead->id));
        $this->assertEquals('Shakil Ahmed Updated', $lead->fresh()->name);
        $this->assertEquals(LeadStage::INTERESTED, $lead->fresh()->stage);
        $this->assertEquals('Chattogram', $lead->fresh()->location);
    }

    public function test_user_can_delete_lead(): void
    {
        $lead = Lead::create([
            'name' => 'Temporary Lead',
            'mobile' => '01311223344',
            'lead_source_id' => $this->source->id,
            'stage' => LeadStage::LOST,
            'temperature' => LeadTemperature::COLD,
            'score' => 0,
            'owner_user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('leads.destroy', $lead->id));

        $response->assertRedirect(route('leads.index'));
        $this->assertSoftDeleted('leads', [
            'id' => $lead->id,
        ]);
    }
}
