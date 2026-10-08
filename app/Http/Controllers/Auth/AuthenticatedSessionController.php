<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditLogger;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('welcome');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $auditLogger->log($request, 'LOGIN_SUCCESS', $request->user(), statusCode: 302);

        return redirect()->route('portal');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $user = $request->user();
        $auditLogger->log($request, 'LOGOUT', $user, statusCode: 302);

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}