<?php

namespace Tests\Feature;

use App\Enums\LeadStage;
use App\Enums\LeadTemperature;
use App\Enums\PresentationOutcome;
use App\Enums\PresentationType;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\TaskType;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SblGrowthManagerTest extends TestCase
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

        $this->source = LeadSource::create([
            'name' => 'Facebook Page',
            'is_active' => true,
            'order' => 1,
        ]);
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Lead Pipeline Funnel');
        $response->assertSee('Follow-ups Today');
    }

    public function test_user_can_create_lead_and_auto_schedule_next_action(): void
    {
        $response = $this->actingAs($this->user)->post('/leads', [
            'name' => 'Monirul Islam',
            'mobile' => '01711223344',
            'whatsapp' => '01711223344',
            'lead_source_id' => $this->source->id,
            'interest_types' => ['E-commerce', 'Dropshipping'],
            'lead_tag' => 'E1',
            'stage' => LeadStage::INTERESTED->value,
            'budget_range' => '50,000 - 100,000 BDT',
            'decision_timeline' => 'Within 15 Days',
            'next_action_type' => 'Follow-up Call',
            'next_action_at' => now()->addDay()->toDateTimeString(),
            'notes' => 'Interested in starting dropshipping store.',
        ]);

        $this->assertDatabaseHas('leads', [
            'name' => 'Monirul Islam',
            'mobile' => '01711223344',
            'stage' => LeadStage::INTERESTED->value,
        ]);

        $lead = Lead::where('mobile', '01711223344')->first();
        $this->assertNotNull($lead);
        $this->assertGreaterThan(0, $lead->score);

        // Verify activity timeline log
        $this->assertDatabaseHas('activities', [
            'lead_id' => $lead->id,
            'type' => 'lead_created',
        ]);

        // Verify task was scheduled
        $this->assertDatabaseHas('tasks', [
            'related_lead_id' => $lead->id,
            'type' => TaskType::FOLLOW_UP->value,
            'status' => TaskStatus::PENDING->value,
        ]);

        $response->assertRedirect(route('leads.show', $lead->id));
    }

    public function test_user_can_transition_lead_stage_and_log_activity(): void
    {
        $lead = Lead::create([
            'name' => 'Sultana Razia',
            'mobile' => '01899887766',
            'lead_source_id' => $this->source->id,
            'stage' => LeadStage::NEW,
            'temperature' => LeadTemperature::COLD,
            'score' => 20,
            'owner_user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->patchJson("/leads/{$lead->id}/stage", [
            'stage' => LeadStage::QUALIFIED->value,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'stage' => LeadStage::QUALIFIED->value,
        ]);

        $this->assertEquals(LeadStage::QUALIFIED, $lead->fresh()->stage);

        // Verify activity was created
        $this->assertDatabaseHas('activities', [
            'lead_id' => $lead->id,
            'type' => 'stage_change',
        ]);
    }

    public function test_user_can_complete_task_with_outcome_and_next_action(): void
    {
        $lead = Lead::create([
            'name' => 'Jashim Uddin',
            'mobile' => '01988776655',
            'lead_source_id' => $this->source->id,
            'stage' => LeadStage::CONTACTED,
            'temperature' => LeadTemperature::WARM,
            'score' => 50,
            'owner_user_id' => $this->user->id,
        ]);

        $task = Task::create([
            'title' => 'Initial Phone Call with Jashim',
            'type' => TaskType::CALL,
            'related_lead_id' => $lead->id,
            'user_id' => $this->user->id,
            'due_at' => now(),
            'priority' => TaskPriority::HIGH,
            'status' => TaskStatus::PENDING,
        ]);

        $response = $this->actingAs($this->user)->post("/tasks/{$task->id}/complete", [
            'outcome' => 'Spoke for 10 mins, requested dropshipping presentation.',
            'next_action' => 'Schedule 1-on-1 Presentation',
            'next_action_at' => now()->addDays(2)->toDateTimeString(),
        ]);

        $response->assertStatus(302);

        $this->assertEquals(TaskStatus::COMPLETED, $task->fresh()->status);
        $this->assertEquals('Spoke for 10 mins, requested dropshipping presentation.', $task->fresh()->outcome);

        // Next task should be created automatically
        $this->assertDatabaseHas('tasks', [
            'related_lead_id' => $lead->id,
            'title' => 'Schedule 1-on-1 Presentation with Jashim Uddin',
            'status' => TaskStatus::PENDING->value,
        ]);
    }

    public function test_user_can_record_presentation(): void
    {
        $lead = Lead::create([
            'name' => 'Akram Hossain',
            'mobile' => '01655443322',
            'lead_source_id' => $this->source->id,
            'stage' => LeadStage::INTERESTED,
            'temperature' => LeadTemperature::WARM,
            'score' => 60,
            'owner_user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post('/presentations', [
            'lead_id' => $lead->id,
            'date_time' => now()->toDateTimeString(),
            'type' => PresentationType::ONLINE->value,
            'topic' => 'SBL Ecosystem Presentation',
            'questions' => 'What is the required investment amount?',
            'objections' => 'Need to consult with family partner',
            'outcome' => PresentationOutcome::HOT->value,
            'next_follow_up_at' => now()->addDay()->toDateTimeString(),
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('presentations', [
            'lead_id' => $lead->id,
            'type' => PresentationType::ONLINE->value,
            'outcome' => PresentationOutcome::HOT->value,
        ]);

        // Verify lead stage updated to presentation
        $this->assertEquals(LeadStage::PRESENTATION, $lead->fresh()->stage);
    }

    public function test_user_can_view_reports_and_content_calendar(): void
    {
        $reportsRes = $this->actingAs($this->user)->get('/reports');
        $reportsRes->assertStatus(200);
        $reportsRes->assertSee('Lead Funnel Distribution');

        $calendarRes = $this->actingAs($this->user)->get('/marketing/content-calendar');
        $calendarRes->assertStatus(200);
        $calendarRes->assertSee('Content Calendar');
    }
}

