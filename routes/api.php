<?php

use App\Models\CalendarActivity;
use App\Models\User;
use App\Models\VaptSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'username' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    $user = User::where('username', $credentials['username'])->first();

    if (! $user || ! Hash::check($credentials['password'], $user->password)) {
        throw ValidationException::withMessages([
            'username' => ['The provided credentials are incorrect.'],
        ]);
    }

    return response()->json([
        'token' => $user->createToken('mobile-app')->plainTextToken,
        'user' => [
            'id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
        ],
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
Route::get('/websites', function (Request $request) {
    $websites = VaptSystem::all()->map(function ($site) {
        return [
            'id' => $site->id,
            'name' => $site->name,
            'url' => $site->url,
            'status' => $site->status,
            'error_message' => $site->remarks,
            'logo_url' => null,
            'last_checked' => $site->updated_at->diffForHumans(),
        ];
    });

    return response()->json([
        'success' => true,
        'count' => $websites->count(),
        'data' => $websites
    ], 200);
});

Route::get('/calendar', function () {
    $activities = CalendarActivity::query()
        ->orderBy('start_time')
        ->get()
        ->map(fn (CalendarActivity $activity) => [
            'id' => $activity->id,
            'type' => $activity->type,
            'title' => $activity->agenda ?? $activity->location ?? $activity->type,
            'agenda' => $activity->agenda,
            'start_time' => $activity->start_time?->toIso8601String(),
            'end_time' => $activity->end_time?->toIso8601String(),
            'presiding_officer' => $activity->presiding_officer,
            'attendees' => $activity->attendees,
            'venue' => $activity->venue,
            'personnel' => $activity->personnel,
            'location' => $activity->location,
            'note' => $activity->note,
            'completed_at' => $activity->completed_at?->toIso8601String(),
        ]);

    return response()->json([
        'success' => true,
        'count' => $activities->count(),
        'data' => $activities,
    ]);
});

Route::get('/calendar/{activity}', function (CalendarActivity $activity) {
    return response()->json([
        'success' => true,
        'data' => $activity,
    ]);
});
});
