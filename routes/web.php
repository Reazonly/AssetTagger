<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserMasterController;
use App\Http\Controllers\AssetUserController;
 // Ditambahkan

// Authentication Routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Public Routes
Route::get('assets/{asset}/public', [AssetController::class, 'publicShow'])->name('assets.public.show');
Route::get('assets/{asset}/pdf', [AssetController::class, 'downloadPDF'])->name('assets.pdf');
Route::get('/assets/get-units/{category}', [AssetController::class, 'getUnits'])->name('assets.getUnits');

// Main application routes (protected by auth middleware)
Route::middleware('auth')->group(function () {
    // Dashboard bisa diakses semua role yang sudah login
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // --- FITUR PROFIL DITAMBAHKAN DI SINI ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // --- AKHIR PENAMBAHAN ---

    // Grup untuk role yang bisa MEMODIFIKASI ASET (Admin dan Editor)
    Route::middleware(['role:admin,editor'])->group(function () {
        // Aset
        Route::post('assets/import', [AssetController::class, 'import'])->name('assets.import');
        Route::get('assets/create', [AssetController::class, 'create'])->name('assets.create');
        Route::post('assets', [AssetController::class, 'store'])->name('assets.store');
        Route::get('assets/{asset}/edit', [AssetController::class, 'edit'])->name('assets.edit');
        Route::put('assets/{asset}', [AssetController::class, 'update'])->name('assets.update');
        Route::delete('assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');
    });

    // Grup KHUSUS untuk ADMIN AREA (Manajemen Pengguna & Master Data)
    Route::middleware(['role:admin'])->group(function() {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/update-role', [UserController::class, 'updateRole'])->name('users.updateRole');
        
        // --- FITUR HAPUS & RESET PASSWORD DITAMBAHKAN DI SINI ---
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');
        // --- AKHIR PENAMBAHAN ---
        
        Route::prefix('master-data')->name('master-data.')->group(function () {
            Route::resource('categories', CategoryController::class)->except(['show']);
            Route::resource('companies', CompanyController::class)->except(['show']);
            Route::resource('asset-users', AssetUserController::class)->except(['show']);
        });
    });

    // Grup untuk role yang bisa MELIHAT data (Semua role login)
    Route::middleware(['role:admin,viewer,editor,user'])->group(function () {
        Route::get('assets', [AssetController::class, 'index'])->name('assets.index');
        Route::get('assets/print', [AssetController::class, 'print'])->name('assets.print');
        Route::get('assets/export', [AssetController::class, 'export'])->name('assets.export');
        Route::get('assets/{asset}', [AssetController::class, 'show'])->name('assets.show'); 
    });
});