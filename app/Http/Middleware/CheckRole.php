<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        // Superadmin bypass for admin routes
        if ($role === 'admin' && ($user->isSuperAdmin() || $user->isAdmin())) {
            return $next($request);
        }

        if ($role === 'superadmin' && ! $user->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Superadmin privileges required.');
        }

        return $next($request);
    }
}