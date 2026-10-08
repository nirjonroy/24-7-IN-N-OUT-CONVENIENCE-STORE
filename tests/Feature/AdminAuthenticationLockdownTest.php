<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthenticationLockdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_seeder_creates_hashed_login_account(): void
    {
        $this->seed(AdminUserSeeder::class);

        $admin = User::where('email', config('admin.email'))->first();

        $this->assertNotNull($admin);
        $this->assertNotSame('12345678', $admin->password);
        $this->assertTrue(Hash::check('12345678', $admin->password));
    }

    public function test_seeded_admin_can_login_and_reaches_admin_dashboard(): void
    {
        $this->seed(AdminUserSeeder::class);

        $response = $this->post('/login', [
            'email' => config('admin.email'),
            'password' => '12345678',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin');
    }

    public function test_seeded_admin_wrong_password_fails(): void
    {
        $this->seed(AdminUserSeeder::class);

        $this->post('/login', [
            'email' => config('admin.email'),
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_guest_admin_access_redirects_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_non_admin_authenticated_user_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create([
            'email' => 'other@example.com',
        ]);

        foreach (['/admin', '/admin/pages', '/admin/businesses', '/admin/catalog/categories', '/admin/seo/settings'] as $uri) {
            $this->actingAs($user)->get($uri)->assertForbidden();
        }
    }

    public function test_public_frontend_and_contact_repair_endpoints_remain_public(): void
    {
        $this->get('/')->assertOk();
        $this->get('/about')->assertOk();
        $this->get('/contact')->assertOk();
        $this->get('/phone-repair')->assertOk();
        $this->get('/smoothies')->assertOk();
        $this->getJson('/phone-repair/models')->assertUnprocessable();
        $this->getJson('/phone-repair/estimate')->assertUnprocessable();

        $this->post('/contact', [
            'name' => 'Site Visitor',
            'email' => 'visitor@example.com',
            'topic' => 'General',
            'message' => 'I have a question about the store.',
        ])->assertRedirect();
    }
}
