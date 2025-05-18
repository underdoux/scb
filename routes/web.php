<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialAccountController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Social Account Management
    Route::get('/social-accounts', [SocialAccountController::class, 'index'])->name('social-accounts.index');
    Route::get('/social-accounts/{platform}/connect', [SocialAuthController::class, 'redirect'])->name('social-accounts.connect');
    Route::get('/social-accounts/{platform}/callback', [SocialAuthController::class, 'callback'])->name('social-accounts.callback');
    Route::delete('/social-accounts/{platform}', [SocialAuthController::class, 'disconnect'])->name('social-accounts.disconnect');
});

require __DIR__.'/auth.php';
