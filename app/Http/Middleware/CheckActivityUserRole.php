<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActivityUserRole
{
    public function handle(Request $request, Closure $next): Response
    {
        // Restrict both Activity User & Activity Admin to Calendar routes
        if (auth()->check() && auth()->user()->isCalendarOnlyUser()) {
            $allowedRoutes = ['calendar', 'calendar.*', 'logout', 'profile.*'];

            if (!$request->routeIs($allowedRoutes)) {
                return redirect()->route('calendar');
            }
        }

        return $next($request);
    }
}