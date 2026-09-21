<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VaptSystemController;
use App\Http\Controllers\ViewerController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentTrackingLoginController;
use App\Http\Middleware\CheckRole;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckActivityUserRole;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', function () {
    return view('welcome');
})->name('login');

Route::middleware(['auth'])->group(function () {

    // Monitoring site selection portal
    Route::get('/portal', function () {
        return view('portal');
    })->name('portal');

    // Dedicated Viewer Module Route
    Route::get('/viewer/dashboard', [ViewerController::class, 'index'])->name('viewer.dashboard');

    // Document Tracking Module
    Route::post('/documents/login', [DocumentTrackingLoginController::class, 'login'])->name('documents.login.store');
    Route::post('/documents/logout', [DocumentTrackingLoginController::class, 'logout'])->name('documents.logout');
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/{document}/view', [DocumentController::class, 'view'])->name('documents.view');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::put('/documents/{document}', [DocumentController::class, 'update'])->name('documents.update');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Export Route (Shared)
    Route::get('/vapt/export', [VaptSystemController::class, 'exportCsv'])->name('vapt.export');

    // Main Dashboard & Monitored Systems Management (ADMIN & SUPERADMIN ONLY)
    Route::middleware([CheckRole::class . ':admin'])->group(function () {
        Route::get('/dashboard', [VaptSystemController::class, 'dashboard'])->name('dashboard');
        Route::get('/vapt', [VaptSystemController::class, 'index'])->name('vapt.index');
        Route::post('/vapt', [VaptSystemController::class, 'store'])->name('vapt.store');
        Route::put('/vapt/{vapt}', [VaptSystemController::class, 'update'])->name('vapt.update');
        Route::delete('/vapt/{vapt}', [VaptSystemController::class, 'destroy'])->name('vapt.destroy');
    });

    // User Management & Role Assignment (SUPERADMIN ONLY)
    Route::middleware([CheckRole::class . ':superadmin'])->group(function () {
        Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
        Route::post('/register', [RegisteredUserController::class, 'store']);
        Route::put('/register/users/{user}', [RegisteredUserController::class, 'update'])->name('users.update');
        Route::post('/register/users/{user}/reset-password', [RegisteredUserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('/register/users/{user}', [RegisteredUserController::class, 'destroy'])->name('users.destroy');
    });
    Route::get('/calendar', [VaptSystemController::class, 'calendar'])->name('calendar');    
    // Calendar Activity Routes
    Route::post('/calendar/activity', [VaptSystemController::class, 'storeActivity'])->name('calendar.activity.store');
    Route::put('/calendar/activity/{id}', [VaptSystemController::class, 'updateActivity'])->name('calendar.activity.update');
    Route::delete('/calendar/activity/{id}', [VaptSystemController::class, 'destroyActivity'])->name('calendar.activity.destroy');
    Route::get('/calendar/activity/{id}/reference', [VaptSystemController::class, 'downloadActivityReference'])->name('calendar.activity.reference');
    Route::post('/calendar/activity/{id}/complete', [VaptSystemController::class, 'completeActivity'])->name('calendar.activity.complete');
    Route::get('/calendar/activity/{id}/completion-reference', [VaptSystemController::class, 'downloadCompletionReference'])->name('calendar.activity.completion-reference');
});


require __DIR__.'/auth.php';