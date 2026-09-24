<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('HealthCare Plus');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password' => Hash::make('password123'),
        ]);

        $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_inactive_users_cannot_login(): void
    {
        User::create([
            'name' => 'Inactive Cashier',
            'email' => 'inactive@test.com',
            'role' => User::ROLE_CASHIER,
            'status' => 'inactive',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@test.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
    }

    public function test_authenticated_user_cannot_access_login_screen(): void
    {
        $user = User::create([
            'name' => 'Active Admin',
            'email' => 'activeadmin@test.com',
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_login_screen_respects_x_forwarded_proto_header_for_secure_urls(): void
    {
        $response = $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Port' => '443',
        ])->get('/login');

        $response->assertStatus(200);
        $response->assertSee('action="https://', false);
    }
}
