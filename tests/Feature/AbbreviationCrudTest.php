<?php

namespace Tests\Feature;

use App\Models\Abbreviation;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbbreviationCrudTest extends TestCase
{
    use RefreshDatabase;
    public function test_admin_can_create_update_and_delete_without_losing_defaults(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $this->actingAs(User::where('email', 'admin@sbl.test')->first());
        $response = $this->get('/abbreviations')->assertOk();
        $response->assertSee('No');
        $response->assertSee('Short Form');
        $response->assertSee('Abbreviation');
        $response->assertSee('Use Case');
        $response->assertSee('Action');
        $response->assertDontSee('E-Commerce Core (');

        $count = Abbreviation::count();
        $data = ['code' => "QA'T", 'name' => 'Test term', 'meaning_bn' => 'Test meaning', 'description_bn' => 'English usage test', 'icon' => '', 'tag' => ''];
        $id = $this->postJson('/abbreviations', $data)->assertCreated()->assertJsonPath('category_slug', 'ecommerce')->json('id');
        $this->postJson('/abbreviations', $data)->assertUnprocessable()->assertJsonValidationErrors('code');
        $this->putJson('/abbreviations/' . $id, array_merge($data, ['name' => 'Updated']))->assertOk()->assertJsonPath('name', 'Updated');
        $this->assertDatabaseHas('abbreviations', ['id' => $id, 'name' => 'Updated', 'category_slug' => 'ecommerce']);
        $this->deleteJson('/abbreviations/' . $id)->assertNoContent();
        $this->assertDatabaseCount('abbreviations', $count);
        $this->deleteJson('/abbreviations/' . $id)->assertNotFound();
    }
    public function test_read_only_users_and_guests_cannot_write(): void
    {
        $this->postJson('/abbreviations', [])->assertUnauthorized();
        $this->actingAs(User::factory()->create(['status' => 'active']));
        $this->get('/abbreviations')->assertOk();
        $this->postJson('/abbreviations', [])->assertForbidden();
        $term = Abbreviation::first();
        $this->putJson('/abbreviations/' . $term->id, [])->assertForbidden();
        $this->deleteJson('/abbreviations/' . $term->id)->assertForbidden();
    }
    public function test_invalid_input_is_rejected(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $this->actingAs(User::where('email', 'admin@sbl.test')->first());
        $this->postJson('/abbreviations', ['code' => str_repeat('a', 51), 'category_slug' => 'unknown'])->assertUnprocessable()->assertJsonValidationErrors(['code', 'name', 'category_slug', 'meaning_bn', 'description_bn']);
    }
}
