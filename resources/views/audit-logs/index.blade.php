<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Account Audit Logs') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-800">Account activity</h3>
                        <p class="mt-1 text-sm text-gray-500">Authenticated page and API requests, account changes, and failed sign-in attempts.</p>
                    </div>
                    <a href="{{ route('register') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">User management</a>
                </div>

                <form method="GET" action="{{ route('audit-logs.index') }}" class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div>
                        <label for="account" class="block text-xs font-semibold uppercase tracking-wider text-gray-600">Account</label>
                        <select id="account" name="account" class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All accounts</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account }}" @selected(($filters['account'] ?? '') === $account)>{{ $account }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="action" class="block text-xs font-semibold uppercase tracking-wider text-gray-600">Event</label>
                        <select id="action" name="action" class="mt-1 w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All events</option>
                            @foreach ($actions as $action)
                                <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Filter</button>
                        <a href="{{ route('audit-logs.index') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">Clear</a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Time</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Account</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Event</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Request</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Result</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">IP address</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse ($logs as $log)
                                <tr class="align-top hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-4 py-3 text-xs text-gray-500">{{ $log->created_at->format('M d, Y h:i:s A') }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $log->actor_username ?? $log->user?->username ?? $log->target_user }}</td>
                                    <td class="px-4 py-3"><span class="rounded-full bg-indigo-50 px-2 py-1 text-xs font-bold text-indigo-700">{{ $log->action }}</span></td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600">
                                        @if ($log->method || $log->path)
                                            <span class="font-bold">{{ $log->method }}</span> {{ $log->path }}
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs {{ $log->status_code >= 400 ? 'font-semibold text-red-600' : 'text-gray-600' }}">{{ $log->status_code ?? '—' }}</td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $log->ip_address ?? '—' }}</td>
                                    <td class="max-w-xs px-4 py-3 text-xs text-gray-500">
                                        <div>{{ $log->details ?? '—' }}</div>
                                        @if ($log->user_agent)
                                            <div class="mt-1 break-all text-[10px] text-gray-400">{{ $log->user_agent }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-400">No account activity found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-100 px-4 py-3">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
