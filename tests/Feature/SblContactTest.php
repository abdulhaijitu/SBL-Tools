<?php

namespace Tests\Feature;

use App\Models\SblContact;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\SblContactSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SblContactTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(SblContactSeeder::class);

        $this->admin = User::where('email', 'admin@sbl.test')->first();
    }

    public function test_authenticated_user_can_view_contacts_directory(): void
    {
        $response = $this->actingAs($this->admin)->get(route('contacts.index'));

        $response->assertOk();
        $response->assertSee('SBL Contacts & WhatsApp Directory');
        $response->assertSee('কাস্টমার কেয়ার ও সাপোর্ট সেল');
        $response->assertSee('01700000000');
        $response->assertSee('tel:01700000000');
        $response->assertSee('https://wa.me/8801700000000', false);
    }

    public function test_admin_can_create_new_contact(): void
    {
        $response = $this->actingAs($this->admin)->post(route('contacts.store'), [
            'department' => 'জরুরি সাপোর্ট হটলাইন',
            'contact_person' => 'ডিউটি ম্যানেজার',
            'phone' => '01300000099',
            'whatsapp' => '01300000099',
            'email' => 'emergency@sbl.com.bd',
            'available_hours' => '২৪ ঘণ্টা',
            'description' => 'জরুরি যোগাযোগ ও এসওএস সেবা।',
            'icon' => '🚨',
            'badge' => 'জরুরি সেবা',
            'is_primary' => '1',
            'sort_order' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sbl_contacts', [
            'department' => 'জরুরি সাপোর্ট হটলাইন',
            'phone' => '01300000099',
            'whatsapp' => '01300000099',
        ]);
    }

    public function test_admin_can_update_contact(): void
    {
        $contact = SblContact::first();

        $response = $this->actingAs($this->admin)->put(route('contacts.update', $contact), [
            'department' => 'আপডেটেড কাস্টমার কেয়ার সেল',
            'contact_person' => 'সিনিয়র সাপোর্ট স্পেশালিস্ট',
            'phone' => '01711223344',
            'whatsapp' => '01711223344',
            'email' => 'support_updated@sbl.com.bd',
            'available_hours' => '২৪/৭',
            'description' => 'আপডেটেড তথ্য বিবরণী।',
            'icon' => '📞',
            'badge' => 'হটলাইন',
            'sort_order' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sbl_contacts', [
            'id' => $contact->id,
            'department' => 'আপডেটেড কাস্টমার কেয়ার সেল',
            'phone' => '01711223344',
        ]);
    }

    public function test_admin_can_delete_contact(): void
    {
        $contact = SblContact::create([
            'department' => 'টেম্প ডিলিট ডিপার্টমেন্ট',
            'phone' => '01999999999',
            'whatsapp' => '01999999999',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('contacts.destroy', $contact));

        $response->assertRedirect();
        $this->assertDatabaseMissing('sbl_contacts', [
            'id' => $contact->id,
        ]);
    }
}
