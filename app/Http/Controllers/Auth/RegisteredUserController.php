<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display User Management view with users and audit logs.
     */
    public function create(): View
    {
        $users = User::latest()->get();
        $logs = ActivityLog::with('user')->latest()->take(20)->get(); // Fetch latest 20 audit events

        return view('auth.register', compact('users', 'logs'));
    }

    /**
     * Register new user and log activity.
     */
    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'role'     => ['required', Rule::in(User::validRoles())],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $newUser = User::create([
            'username' => $request->username,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
        ]);

        // Audit Log
        $auditLogger->log($request, 'CREATE_USER', $request->user(), $newUser->username, "Assigned role: {$newUser->role}", 302);

        return redirect()->route('register')->with('success', "User account '{$newUser->username}' created successfully.");
    }

    /**
     * Update user details and log activity.
     */
    public function update(Request $request, User $user, AuditLogger $auditLogger): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'     => ['required', Rule::in(User::validRoles())],
        ]);

        $oldRole = $user->role;
        $user->username = $request->username;
        $user->role = $request->role;
        $user->save();

        // Audit Log
        $auditLogger->log($request, 'UPDATE_USER', $request->user(), $user->username, "Role updated from '{$oldRole}' to '{$user->role}'", 302);

        return redirect()->route('register')->with('success', "Account for '{$user->username}' updated successfully.");
    }

    /**
     * Quick Password Reset method for Superadmin.
     */
    public function resetPassword(Request $request, User $user, AuditLogger $auditLogger): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        // Audit Log
        $auditLogger->log($request, 'RESET_PASSWORD', $request->user(), $user->username, 'Password forcibly reset by Superadmin', 302);

        return redirect()->route('register')->with('success', "Password for '{$user->username}' has been reset successfully.");
    }

    /**
     * Delete user and log activity.
     */
    public function destroy(Request $request, User $user, AuditLogger $auditLogger): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('register')->with('error', 'You cannot delete your own account while logged in.');
        }

        $targetUsername = $user->username;
        $user->delete();

        // Audit Log
        $auditLogger->log($request, 'DELETE_USER', $request->user(), $targetUsername, 'Account permanently deleted', 302);

        return redirect()->route('register')->with('success', "Account '{$targetUsername}' deleted successfully.");
    }
}