<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentPushSubscription;
use App\Models\User;
use App\Services\DocumentPushSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class DocumentPushTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['document-alerts.public_key' => 'test', 'document-alerts.private_key' => 'test']);
    }

    private function payload(): array
    {
        return ['endpoint' => 'https://fcm.googleapis.com/fcm/send/test',
            'keys' => ['p256dh' => str_repeat('A', 87), 'auth' => str_repeat('B', 22)]];
    }

    private function login(): User
    {
        $portal = User::factory()->create(['role' => User::ROLE_LEGACY_VIEWER]);
        $office = User::factory()->create(['role' => User::ROLE_POIC_SMSB]);
        $this->actingAs($portal)->withSession([
            'document_tracking_session' => true, 'document_tracking_user_id' => $office->id,
        ]);

        return $office;
    }

    public function test_subscription_belongs_to_module_account_and_can_be_disabled(): void
    {
        $office = $this->login();
        $this->postJson('/documents/push-subscriptions', $this->payload())->assertOk();
        $this->postJson('/documents/push-subscriptions', $this->payload())->assertOk();
        $this->assertDatabaseCount('document_push_subscriptions', 1);
        $this->assertDatabaseHas('document_push_subscriptions', ['user_id' => $office->id]);
        $this->postJson('/documents/push-subscriptions/status', $this->payload())
            ->assertJsonPath('subscribed', true);
        $this->deleteJson('/documents/push-subscriptions', $this->payload())->assertNoContent();
        $this->assertDatabaseCount('document_push_subscriptions', 0);
    }

    public function test_subscription_requires_module_access_and_a_real_push_provider(): void
    {
        $this->postJson('/documents/push-subscriptions', $this->payload())->assertUnauthorized();
        $office = $this->login();
        foreach (['https://127.0.0.1/push', 'https://fcm.googleapis.com.evil.test/push',
            'http://fcm.googleapis.com/push', 'https://fcm.googleapis.com:8443/push'] as $endpoint) {
            $this->postJson('/documents/push-subscriptions', array_replace($this->payload(), ['endpoint' => $endpoint]))
                ->assertUnprocessable();
        }
        $office->update(['role' => User::ROLE_LEGACY_VIEWER]);
        $this->postJson('/documents/push-subscriptions', $this->payload())->assertForbidden();
    }

    public function test_another_account_cannot_check_or_delete_a_subscription(): void
    {
        $this->login();
        $this->postJson('/documents/push-subscriptions', $this->payload())->assertOk();
        $other = User::factory()->create(['role' => User::ROLE_POIC_ASDB]);
        $this->withSession(['document_tracking_user_id' => $other->id]);
        $this->postJson('/documents/push-subscriptions/status', $this->payload())->assertJsonPath('subscribed', false);
        $this->deleteJson('/documents/push-subscriptions', $this->payload())->assertNoContent();
        $this->assertDatabaseCount('document_push_subscriptions', 1);
    }

    private function subscribeWithDocument(): Document
    {
        $office = $this->login();
        $this->postJson('/documents/push-subscriptions', $this->payload())->assertOk();

        return Document::create(['title' => 'Sensitive document', 'status' => 'Pending',
            'due_date' => today(), 'category' => 'Memo', 'user_id' => $office->id]);
    }

    public function test_sender_deduplicates_daily_and_rechecks_document_status(): void
    {
        $document = $this->subscribeWithDocument();
        $this->mock(DocumentPushSender::class, function (MockInterface $mock) {
            $mock->shouldReceive('send')->twice()->withArgs(function ($subscription, $payload) {
                return ! str_contains($payload['body'], 'Sensitive document');
            })->andReturn('sent');
        });
        $this->artisan('documents:send-deadline-alerts')->assertSuccessful();
        $this->artisan('documents:send-deadline-alerts')->assertSuccessful();
        $this->travel(1)->days();
        $this->artisan('documents:send-deadline-alerts')->assertSuccessful();
        $document->update(['status' => 'Approved']);
        $this->travel(1)->days();
        $this->artisan('documents:send-deadline-alerts')->assertSuccessful();
    }

    public function test_failed_delivery_retries_and_expired_subscriptions_are_removed(): void
    {
        $this->subscribeWithDocument();
        $this->mock(DocumentPushSender::class, fn (MockInterface $mock) => $mock->shouldReceive('send')->twice()->andReturn('failed', 'expired'));
        $this->artisan('documents:send-deadline-alerts')->assertFailed();
        $this->assertNull(DocumentPushSubscription::first()->last_digest);
        $this->artisan('documents:send-deadline-alerts')->assertSuccessful();
        $this->assertDatabaseCount('document_push_subscriptions', 0);
    }

    public function test_revoked_access_removes_subscription_without_sending(): void
    {
        $document = $this->subscribeWithDocument();
        $document->user->update(['role' => User::ROLE_LEGACY_VIEWER]);
        $this->mock(DocumentPushSender::class, fn (MockInterface $mock) => $mock->shouldNotReceive('send'));
        $this->artisan('documents:send-deadline-alerts')->assertSuccessful();
        $this->assertDatabaseCount('document_push_subscriptions', 0);
    }
}
