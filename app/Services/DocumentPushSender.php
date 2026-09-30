<?php

namespace App\Services;

use App\Models\DocumentPushSubscription;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class DocumentPushSender
{
    public function send(DocumentPushSubscription $subscription, array $payload): string
    {
        $push = new WebPush(['VAPID' => [
            'subject' => config('document-alerts.subject'),
            'publicKey' => config('document-alerts.public_key'),
            'privateKey' => config('document-alerts.private_key'),
        ]], ['TTL' => 3600], 15, ['allow_redirects' => false]);
        $report = $push->sendOneNotification(Subscription::create([
            'endpoint' => $subscription->endpoint,
            'publicKey' => $subscription->public_key,
            'authToken' => $subscription->auth_token,
            'contentEncoding' => 'aes128gcm',
        ]), json_encode($payload, JSON_THROW_ON_ERROR));

        return $report->isSuccess() ? 'sent' : ($report->isSubscriptionExpired() ? 'expired' : 'failed');
    }
}
