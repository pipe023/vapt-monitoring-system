<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'account' => ['nullable', 'string', 'max:255'],
            'action' => ['nullable', 'string', 'max:255'],
        ]);

        $logs = ActivityLog::query()
            ->with('user')
            ->when($filters['account'] ?? null, function ($query, string $account): void {
                $query->where(function ($query) use ($account): void {
                    $query->where('actor_username', $account)
                        ->orWhere('target_user', $account)
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('username', $account));
                });
            })
            ->when($filters['action'] ?? null, fn ($query, string $action) => $query->where('action', $action))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        $accounts = ActivityLog::query()
            ->select('actor_username as username')
            ->whereNotNull('actor_username')
            ->union(
                ActivityLog::query()
                    ->select('target_user as username')
                    ->whereNotNull('target_user')
            )
            ->union(User::query()->select('username'))
            ->orderBy('username')
            ->pluck('username');
        $actions = ActivityLog::query()->distinct()->orderBy('action')->pluck('action');

        return view('audit-logs.index', compact('logs', 'accounts', 'actions', 'filters'));
    }
}
