<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_user_can_access_dashboard_after_login(): void
    {
        $user = User::factory()->create(['phone' => '01833876434', 'status' => 'active']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_user_can_login_with_phone_and_redirect_to_dashboard(): void
    {
        $user = User::factory()->create([
            'phone' => '01833876434',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $response = $this->post('/login', [
            'login' => '01833876434',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');

        $dash = $this->get('/dashboard');
        $dash->assertStatus(200);
    }

    public function test_login_with_non_existent_phone(): void
    {
        $response = $this->post('/login', [
            'login' => '01833876434',
            'password' => 'SomePassword123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }
}
