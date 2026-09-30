<?php

namespace App\Console\Commands;

use App\Models\DocumentPushSubscription;
use App\Services\DocumentDeadlineAlerts;
use App\Services\DocumentPushSender;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendDocumentDeadlineAlerts extends Command
{
    protected $signature = 'documents:send-deadline-alerts';

    protected $description = 'Send browser push reminders for current document deadlines';

    public function handle(DocumentDeadlineAlerts $alerts, DocumentPushSender $sender): int
    {
        if (! config('document-alerts.public_key') || ! config('document-alerts.private_key')) {
            $this->warn('Configure DOCUMENT_PUSH_PUBLIC_KEY and DOCUMENT_PUSH_PRIVATE_KEY first.');

            return self::FAILURE;
        }

        $failed = false;
        DocumentPushSubscription::with('user')->chunkById(100, function ($subscriptions) use ($alerts, $sender, &$failed) {
            foreach ($subscriptions as $subscription) {
                $lock = Cache::lock('document-push:'.$subscription->id, 60);
                if (! $lock->get()) {
                    continue;
                }
                try {
                    $subscription->refresh();
                    if (! $subscription->user?->canAccessDocumentTracking()) {
                        $subscription->delete();

                        continue;
                    }
                    $current = $alerts->forUser($subscription->user);
                    if ($current->isEmpty()) {
                        continue;
                    }
                    $digest = hash('sha256', $current->pluck('key')->implode('|'));
                    if ($subscription->last_digest === $digest) {
                        continue;
                    }
                    $result = $sender->send($subscription, [
                        'title' => 'Document deadline reminder',
                        'body' => $current->count().' current document(s) are overdue or due within 3 days. Open Document Tracking to review.',
                        'url' => route('documents.index'),
                        'tag' => 'document-deadlines',
                    ]);
                    if ($result === 'expired') {
                        $subscription->delete();
                    } elseif ($result === 'sent') {
                        $subscription->update(['last_digest' => $digest]);
                    } else {
                        $failed = true;
                        $this->warn('Push delivery failed for subscription '.$subscription->id.'; will retry next run.');
                    }
                } catch (\Throwable $exception) {
                    $failed = true;
                    // Do not log endpoints, subscription keys, or provider response bodies.
                    $this->warn('Push delivery failed for subscription '.$subscription->id.' ('.get_class($exception).').');
                } finally {
                    $lock->release();
                }
            }
        });

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
