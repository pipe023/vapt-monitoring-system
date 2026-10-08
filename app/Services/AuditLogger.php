<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditLogger
{
    public function log(
        Request $request,
        string $action,
        ?User $actor = null,
        ?string $targetUsername = null,
        ?string $details = null,
        ?int $statusCode = null,
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $actor?->id,
            'actor_username' => $actor?->username,
            'action' => $action,
            'target_user' => Str::limit($targetUsername ?? $actor?->username ?? 'Unknown', 255, ''),
            'details' => $details,
            'ip_address' => $request->ip(),
            'method' => Str::limit($request->method(), 10, ''),
            'path' => Str::limit('/'.ltrim($request->path(), '/'), 255, ''),
            'status_code' => $statusCode,
            'user_agent' => $request->userAgent() ? Str::limit($request->userAgent(), 1000, '') : null,
        ]);
    }
}
