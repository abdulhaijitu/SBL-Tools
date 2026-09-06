<?php

namespace Tests\Feature;

use App\Models\EcosystemLink;
use App\Models\User;
use Database\Seeders\EcosystemLinkSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcosystemDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(EcosystemLinkSeeder::class);

        $this->admin = User::where('email', 'admin@sbl.test')->first();
    }

    public function test_authenticated_user_can_view_ecosystem_directory(): void
    {
        $response = $this->actingAs($this->admin)->get(route('ecosystem.index'));

        $response->assertOk();
        $response->assertSee('SBL Ecosystem Directory');
        $response->assertSee('SBL Official Web Portal');
        $response->assertSee('SBL Dropshipping Marketplace');
    }

    public function test_admin_can_create_ecosystem_link(): void
    {
        $response = $this->actingAs($this->admin)->post(route('ecosystem.store'), [
            'title' => 'SBL Mobile App',
            'url' => 'https://play.google.com/store/apps/details?id=com.sbl',
            'category' => 'Official Portals',
            'badge' => 'Android App',
            'description' => 'Official Android application for SBL Members and dropshippers.',
            'icon' => '📱',
            'sort_order' => 10,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ecosystem_links', [
            'title' => 'SBL Mobile App',
            'url' => 'https://play.google.com/store/apps/details?id=com.sbl',
        ]);
    }

    public function test_admin_can_update_ecosystem_link(): void
    {
        $link = EcosystemLink::first();

        $response = $this->actingAs($this->admin)->put(route('ecosystem.update', $link), [
            'title' => 'SBL Main Portal (Updated)',
            'url' => 'https://sbl.com.bd',
            'category' => 'Official Portals',
            'badge' => 'Verified Hub',
            'description' => 'Updated portal description',
            'icon' => '🌐',
            'sort_order' => 1,
        ]);

        $response->assertRedirect();
        $link->refresh();
        $this->assertEquals('SBL Main Portal (Updated)', $link->title);
    }

    public function test_admin_can_delete_ecosystem_link(): void
    {
        $link = EcosystemLink::first();
        $id = $link->id;

        $response = $this->actingAs($this->admin)->delete(route('ecosystem.destroy', $link));

        $response->assertRedirect();
        $this->assertDatabaseMissing('ecosystem_links', ['id' => $id]);
    }
}
