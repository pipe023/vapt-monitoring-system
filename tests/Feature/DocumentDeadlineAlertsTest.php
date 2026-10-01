<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentDeadlineAlertsTest extends TestCase
{
    use RefreshDatabase;

    public function test_alerts_include_only_current_documents_with_deadlines_in_the_reminder_window(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 21)->startOfDay());
        $user = User::factory()->create(['role' => User::ROLE_POIC_SMSB]);
        foreach ([
            ['Overdue', 'Pending', '2026-09-20'],
            ['Today', 'In Review', '2026-09-21'],
            ['Soon', 'Pending', '2026-09-24'],
            ['Later', 'Pending', '2026-09-25'],
            ['Finished', 'Approved', '2026-09-21'],
            ['Archived', 'Archived', '2026-09-20'],
            ['Undated', 'Pending', null],
        ] as [$title, $status, $dueDate]) {
            Document::create(['title' => $title, 'status' => $status, 'due_date' => $dueDate,
                'category' => 'Memo', 'user_id' => $user->id]);
        }
        Document::create([
            'title' => 'Completed but still Pending',
            'status' => 'Pending',
            'due_date' => '2026-09-21',
            'completed_at' => now(),
            'category' => 'Memo',
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->withSession([
            'document_tracking_session' => true, 'document_tracking_user_id' => $user->id,
        ])->getJson('/documents/deadline-alerts?search=nonexistent&status=Approved')
            ->assertOk()->assertJsonCount(3, 'alerts')
            ->assertJsonPath('alerts.0.label', '1 day overdue')
            ->assertJsonPath('alerts.1.label', 'Due today')
            ->assertJsonPath('alerts.2.label', 'Due in 3 days')
            ->assertJsonMissing(['title' => 'Completed but still Pending']);
    }

    public function test_alerts_use_the_document_account_not_the_portal_account(): void
    {
        $portal = User::factory()->create(['role' => User::ROLE_OPNS]);
        $office = User::factory()->create(['role' => User::ROLE_POIC_SMSB]);
        $other = User::factory()->create(['role' => User::ROLE_POIC_ASDB]);
        foreach ([$office, $other] as $user) {
            Document::create(['title' => $user->role, 'status' => 'Pending',
                'due_date' => today(), 'category' => 'Memo', 'user_id' => $user->id]);
        }
        $this->actingAs($portal)->withSession([
            'document_tracking_session' => true, 'document_tracking_user_id' => $office->id,
        ])->getJson('/documents/deadline-alerts')->assertOk()->assertJsonCount(1, 'alerts')
            ->assertJsonPath('alerts.0.title', User::ROLE_POIC_SMSB);

        $this->withSession(['document_tracking_user_id' => $portal->id])
            ->getJson('/documents/deadline-alerts')->assertJsonCount(2, 'alerts');
    }

    public function test_alerts_require_both_sessions_and_current_module_access(): void
    {
        $this->getJson('/documents/deadline-alerts')->assertUnauthorized();
        $user = User::factory()->create(['role' => User::ROLE_POIC_SMSB]);
        $this->actingAs($user)->getJson('/documents/deadline-alerts')->assertForbidden();
        $this->withSession(['document_tracking_user_id' => $user->id])
            ->getJson('/documents/deadline-alerts')->assertForbidden();
        $user->update(['role' => User::ROLE_LEGACY_VIEWER]);
        $this->withSession(['document_tracking_session' => true])
            ->getJson('/documents/deadline-alerts')->assertForbidden();
    }
}
