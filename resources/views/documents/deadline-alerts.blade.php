<section x-data="documentDeadlineAlerts(@js([
    'alertsUrl' => route('documents.deadline-alerts'),
    'subscribeUrl' => route('documents.push.store'),
    'statusUrl' => route('documents.push.status'),
    'unsubscribeUrl' => route('documents.push.destroy'),
    'workerUrl' => asset('document-push-sw.js'),
    'publicKey' => config('document-alerts.private_key') ? config('document-alerts.public_key') : null,
    'userId' => $documentTrackingUser->id,
]))" class="rounded-2xl border border-amber-200 bg-white p-5 shadow-sm" aria-labelledby="deadline-alerts-heading">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h3 id="deadline-alerts-heading" class="font-semibold text-gray-800">Deadline notifications <span class="ml-2 rounded-full bg-amber-100 px-2 py-0.5 text-sm text-amber-800" x-text="alerts.length"></span></h3>
            <p class="mt-1 text-xs text-gray-500">Pending and In Review documents due within 3 days, today, or overdue.</p>
        </div>
        <button type="button" @click="togglePush()" :disabled="busy || !supported || !config.publicKey || !active" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50" x-text="busy ? 'Please wait…' : (subscribed ? 'Disable notifications' : 'Enable notifications')">Enable notifications</button>
    </div>
    <p class="mt-3 text-xs text-gray-500" x-text="pushMessage" role="status"></p>
    <p class="mt-1 text-xs text-gray-400">When enabled, this browser receives reminders even after you close or sign out of the module. Disable them here on shared devices.</p>
    <p x-show="error" x-text="error" class="mt-3 text-sm text-red-700" role="alert"></p>
    <p x-show="!loaded && !error" class="mt-4 text-sm text-gray-500">Loading deadlines…</p>
    <p x-show="loaded && !alerts.length && !error" class="mt-4 text-sm text-gray-500">No current documents need a deadline reminder.</p>
    <ul class="mt-4 max-h-64 space-y-2 overflow-y-auto" aria-label="Document deadlines">
        <template x-for="alert in alerts" :key="alert.id">
            <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-gray-50 px-3 py-2">
                <a :href="alert.url" class="text-sm font-medium text-gray-800 underline decoration-gray-300 underline-offset-2" x-text="alert.title"></a>
                <span class="text-xs font-semibold" :class="alert.overdue ? 'text-red-700' : 'text-amber-700'"><span x-text="alert.label"></span> · <time :datetime="alert.due_date" x-text="alert.due_date"></time></span>
            </li>
        </template>
    </ul>
</section>
