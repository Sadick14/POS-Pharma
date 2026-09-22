<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_cannot_access_user_management_or_settings(): void
    {
        $cashier = User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@test.com',
            'role' => User::ROLE_CASHIER,
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($cashier)->get(route('users.index'));
        $response->assertStatus(403);

        $responseSettings = $this->actingAs($cashier)->get(route('settings.index'));
        $responseSettings->assertStatus(403);
    }

    public function test_auditor_can_access_reports_and_audit_logs(): void
    {
        $auditor = User::create([
            'name' => 'Auditor User',
            'email' => 'auditor@test.com',
            'role' => User::ROLE_AUDITOR,
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($auditor)->get(route('reports.index'));
        $response->assertStatus(200);

        $responseLogs = $this->actingAs($auditor)->get(route('audit-logs.index'));
        $responseLogs->assertStatus(200);
    }

    public function test_administrator_has_full_access(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($admin)->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($admin)->get(route('users.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('users.create'))->assertStatus(200);
        $this->actingAs($admin)->get(route('users.edit', $admin))->assertStatus(200);
        $this->actingAs($admin)->get(route('settings.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('medicines.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('pos.index'))->assertStatus(200);
    }
}
