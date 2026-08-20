<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
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
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'role'     => ['required', Rule::in(['superadmin', 'admin', 'viewer'])],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $newUser = User::create([
            'username' => $request->username,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
        ]);

        // Audit Log
        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'CREATE_USER',
            'target_user' => $newUser->username,
            'details'     => "Assigned role: {$newUser->role}",
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->route('register')->with('success', "User account '{$newUser->username}' created successfully.");
    }

    /**
     * Update user details and log activity.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'     => ['required', Rule::in(['superadmin', 'admin', 'viewer'])],
        ]);

        $oldRole = $user->role;
        $user->username = $request->username;
        $user->role = $request->role;
        $user->save();

        // Audit Log
        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'UPDATE_USER',
            'target_user' => $user->username,
            'details'     => "Role updated from '{$oldRole}' to '{$user->role}'",
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->route('register')->with('success', "Account for '{$user->username}' updated successfully.");
    }

    /**
     * Quick Password Reset method for Superadmin.
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        // Audit Log
        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'RESET_PASSWORD',
            'target_user' => $user->username,
            'details'     => "Password forcibly reset by Superadmin",
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->route('register')->with('success', "Password for '{$user->username}' has been reset successfully.");
    }

    /**
     * Delete user and log activity.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('register')->with('error', 'You cannot delete your own account while logged in.');
        }

        $targetUsername = $user->username;
        $user->delete();

        // Audit Log
        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'DELETE_USER',
            'target_user' => $targetUsername,
            'details'     => "Account permanently deleted",
            'ip_address'  => $request->ip(),
        ]);

        return redirect()->route('register')->with('success', "Account '{$targetUsername}' deleted successfully.");
    }
}