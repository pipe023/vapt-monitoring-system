<?php

namespace App\Http\Controllers;

use App\Models\DocumentPushSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DocumentPushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $user = $this->documentUser($request);
        abort_unless(config('document-alerts.public_key') && config('document-alerts.private_key'), 503, 'Push notifications are not configured.');
        $data = $request->validate([
            'endpoint' => ['required', 'url:https', 'max:2048'],
            'keys.p256dh' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{87}=?$/'],
            'keys.auth' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{22}(==)?$/'],
        ]);
        $this->validateEndpoint($data['endpoint']);
        $subscription = DocumentPushSubscription::firstOrNew(['endpoint_hash' => hash('sha256', $data['endpoint'])]);
        if ($subscription->user_id !== $user->id) {
            $subscription->last_digest = null;
        }
        $subscription->fill([
            'user_id' => $user->id, 'endpoint' => $data['endpoint'],
            'public_key' => $data['keys']['p256dh'], 'auth_token' => $data['keys']['auth'],
        ])->save();

        return response()->json(['subscribed' => true]);
    }

    public function status(Request $request)
    {
        $user = $this->documentUser($request);
        $data = $request->validate(['endpoint' => ['required', 'string', 'max:2048']]);

        return response()->json(['subscribed' => DocumentPushSubscription::where('user_id', $user->id)
            ->where('endpoint_hash', hash('sha256', $data['endpoint']))->exists()])
            ->header('Cache-Control', 'no-store, private');
    }

    public function destroy(Request $request)
    {
        $user = $this->documentUser($request);
        $data = $request->validate(['endpoint' => ['required', 'string', 'max:2048']]);
        DocumentPushSubscription::where('user_id', $user->id)
            ->where('endpoint_hash', hash('sha256', $data['endpoint']))->delete();

        return response()->noContent();
    }

    private function documentUser(Request $request): User
    {
        $user = User::find($request->session()->get('document_tracking_user_id'));
        abort_unless($request->session()->get('document_tracking_session') && $user?->canAccessDocumentTracking(), 403);

        return $user;
    }

    private function validateEndpoint(string $endpoint): void
    {
        $parts = parse_url($endpoint);
        $host = strtolower($parts['host'] ?? '');
        $allowed = $host === 'fcm.googleapis.com'
            || $host === 'updates.push.services.mozilla.com'
            || str_ends_with($host, '.push.services.mozilla.com')
            || $host === 'web.push.apple.com'
            || str_ends_with($host, '.notify.windows.com');
        if (! $allowed || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])
            || (isset($parts['port']) && $parts['port'] !== 443)) {
            throw ValidationException::withMessages(['endpoint' => 'Unsupported browser push service.']);
        }
    }
}
