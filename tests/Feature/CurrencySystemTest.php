<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencySystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_default_currency_is_usd(): void
    {
        $this->assertEquals('USD', CurrencyService::getCurrency());
        $this->assertEquals('$', CurrencyService::getSymbol());
        $this->assertEquals('$100', CurrencyService::format(100));
    }

    public function test_currency_conversion_to_bdt(): void
    {
        $this->assertEquals(12000.0, CurrencyService::convertFromUsd(100, 'BDT'));
        $this->assertEquals('12,000 ৳', CurrencyService::format(100, 'BDT'));
    }

    public function test_authenticated_user_can_switch_currency_via_api(): void
    {
        $user = User::where('email', 'admin@sbl.test')->first();

        $response = $this->actingAs($user)->postJson('/currency/switch', [
            'currency' => 'BDT'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'currency' => 'BDT',
                'symbol' => '৳',
                'rate' => 120.0
            ]);

        $this->assertEquals('BDT', session('currency'));
    }

    public function test_currency_switch_via_get_redirect(): void
    {
        $user = User::where('email', 'admin@sbl.test')->first();

        $response = $this->actingAs($user)->get('/currency/BDT');
        $response->assertRedirect();
        $response->assertCookie('sbl_currency', 'BDT');
    }
}
