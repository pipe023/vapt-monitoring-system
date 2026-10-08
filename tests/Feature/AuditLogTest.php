<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_requests_are_recorded_and_visible_to_superadmins(): void
    {
        $superadmin = User::factory()->create([
            'username' => 'ADM001',
            'role' => User::ROLE_LEGACY_SUPERADMIN,
        ]);

        $this->actingAs($superadmin)->get('/portal')->assertOk();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $superadmin->id,
            'actor_username' => 'ADM001',
            'action' => 'ACCOUNT_REQUEST',
            'method' => 'GET',
            'path' => '/portal',
            'status_code' => 200,
        ]);

        $this->actingAs($superadmin)
            ->get('/audit-logs')
            ->assertOk()
            ->assertSee('Account activity')
            ->assertSee('ADM001');
    }

    public function test_non_superadmins_cannot_view_audit_logs(): void
    {
        $user = User::factory()->create([
            'username' => 'USR001',
            'role' => User::ROLE_POIC_SMSB,
        ]);

        $this->actingAs($user)->get('/audit-logs')->assertForbidden();

        $this->assertDatabaseHas('activity_logs', [
            'actor_username' => 'USR001',
            'path' => '/audit-logs',
            'status_code' => 403,
        ]);
    }

    public function test_successful_sign_ins_and_sign_outs_are_recorded(): void
    {
        User::factory()->create([
            'username' => 'USR001',
            'password' => bcrypt('correct-password'),
        ]);

        $this->post('/login', [
            'username' => 'USR001',
            'password' => 'correct-password',
        ])->assertRedirect(route('portal', absolute: false));

        $this->assertDatabaseHas('activity_logs', [
            'actor_username' => 'USR001',
            'action' => 'LOGIN_SUCCESS',
            'path' => '/login',
        ]);

        $this->post('/logout')->assertRedirect('/');

        $this->assertDatabaseHas('activity_logs', [
            'actor_username' => 'USR001',
            'action' => 'LOGOUT',
            'path' => '/logout',
        ]);
    }

    public function test_sanctum_api_requests_are_recorded(): void
    {
        $user = User::factory()->create([
            'username' => 'USR001',
        ]);

        $this->actingAs($user)->getJson('/api/calendar')->assertOk();

        $this->assertDatabaseHas('activity_logs', [
            'actor_username' => 'USR001',
            'action' => 'ACCOUNT_REQUEST',
            'method' => 'GET',
            'path' => '/api/calendar',
            'status_code' => 200,
        ]);
    }

    public function test_failed_web_sign_ins_are_recorded_without_credentials(): void
    {
        User::factory()->create([
            'username' => 'USR001',
            'password' => bcrypt('correct-password'),
        ]);

        $this->post('/login', [
            'username' => 'USR001',
            'password' => 'wrong-password',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'LOGIN_FAILED',
            'target_user' => 'USR001',
            'details' => 'Invalid credentials',
            'method' => 'POST',
            'path' => '/login',
        ]);
        $this->assertDatabaseMissing('activity_logs', ['details' => 'wrong-password']);
    }

    public function test_audit_records_remain_when_an_actor_account_is_deleted(): void
    {
        $user = User::factory()->create([
            'username' => 'USR001',
        ]);
        $log = ActivityLog::create([
            'user_id' => $user->id,
            'actor_username' => $user->username,
            'action' => 'ACCOUNT_REQUEST',
            'target_user' => $user->username,
        ]);

        $user->delete();

        $this->assertDatabaseHas('activity_logs', [
            'id' => $log->id,
            'user_id' => null,
            'actor_username' => 'USR001',
        ]);
    }
}
