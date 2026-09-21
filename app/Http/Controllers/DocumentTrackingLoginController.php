<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DocumentTrackingLoginController extends Controller
{
    public function show()
    {
        $user = User::find(session('document_tracking_user_id'));

        if ($user && $user->canAccessDocumentTracking() && session('document_tracking_session')) {
            return redirect()->route('documents.index');
        }

        return view('documents.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['These credentials do not match our records.'],
            ]);
        }

        if (! $user->canAccessDocumentTracking()) {
            throw ValidationException::withMessages([
                'username' => ['This account does not have access to the Document Tracking module.'],
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('document_tracking_session', true);
        $request->session()->put('document_tracking_user_id', $user->id);

        return redirect()->route('documents.index');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['document_tracking_session', 'document_tracking_user_id']);

        return redirect()->route('documents.index')->with('success', 'Document Tracking session closed.');
    }
}
