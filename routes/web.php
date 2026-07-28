<?php

use App\Http\Controllers\ProfileController;
// 1. ADD THIS LINE RIGHT HERE:
use App\Http\Controllers\VaptSystemController; 
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->middleware('guest')->name('welcome.login');

// Default Breeze Dashboard route (You can replace this with our custom one below)
// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // 2. VAPT Routes
    Route::get('/dashboard', [VaptSystemController::class, 'dashboard'])->name('dashboard');
    Route::get('/vapt/export', [VaptSystemController::class, 'exportCsv'])->name('vapt.export');
    Route::resource('vapt', VaptSystemController::class)->except(['create', 'edit', 'show']);

    // Default Breeze Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
