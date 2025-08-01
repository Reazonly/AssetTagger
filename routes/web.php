<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController; // <-- Add this

// Authentication Routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Public QR Code Route
Route::get('assets/{asset}/public', [AssetController::class, 'publicShow'])->name('assets.public.show');

// Main application routes (protected by auth middleware)
Route::middleware('auth')->group(function () {
    // Root route now points to the dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('assets/import', [AssetController::class, 'import'])->name('assets.import');
    Route::get('assets/print', [AssetController::class, 'print'])->name('assets.print');
    Route::get('assets/export', [AssetController::class, 'export'])->name('assets.export');
    Route::resource('assets', AssetController::class);
});