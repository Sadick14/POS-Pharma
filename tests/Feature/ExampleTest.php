<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::create([
            'name' => 'Pharmacist',
            'email' => 'pharm@test.com',
            'role' => User::ROLE_PHARMACIST,
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
    }
}
