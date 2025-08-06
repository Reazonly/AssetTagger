<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;

// Authentication Routes (Tidak ada perubahan)
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Public Routes (Tidak ada perubahan)
Route::get('assets/{asset}/public', [AssetController::class, 'publicShow'])->name('assets.public.show');
Route::get('assets/{asset}/pdf', [AssetController::class, 'downloadPDF'])->name('assets.pdf');
Route::get('/assets/get-units/{category}', [AssetController::class, 'getUnits'])->name('assets.getUnits');

// --- PERUBAHAN UTAMA DIMULAI DI SINI ---
// Main application routes (protected by auth middleware)
Route::middleware('auth')->group(function () {
    // Dashboard bisa diakses semua role yang sudah login
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Grup untuk role yang bisa MELIHAT data (Admin dan Viewer)
    Route::middleware(['role:admin,viewer'])->group(function () {
        Route::get('assets', [AssetController::class, 'index'])->name('assets.index');
        Route::get('assets/{asset}', [AssetController::class, 'show'])->name('assets.show');
        Route::get('assets/print', [AssetController::class, 'print'])->name('assets.print');
        Route::get('assets/export', [AssetController::class, 'export'])->name('assets.export');
    });

    // Grup KHUSUS untuk role yang bisa MEMODIFIKASI data (Hanya Admin)
    Route::middleware(['role:admin'])->group(function () {
        Route::post('assets/import', [AssetController::class, 'import'])->name('assets.import');
        Route::get('assets/create', [AssetController::class, 'create'])->name('assets.create');
        Route::post('assets', [AssetController::class, 'store'])->name('assets.store');
        Route::get('assets/{asset}/edit', [AssetController::class, 'edit'])->name('assets.edit');
        Route::put('assets/{asset}', [AssetController::class, 'update'])->name('assets.update');
        Route::delete('assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');
    });
});