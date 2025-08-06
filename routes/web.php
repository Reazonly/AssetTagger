<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
// Impor controller master data
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CategoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

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
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('assets/import', [AssetController::class, 'import'])->name('assets.import');
    Route::get('assets/print', [AssetController::class, 'print'])->name('assets.print');
    Route::get('assets/export', [AssetController::class, 'export'])->name('assets.export');
    Route::resource('assets', AssetController::class);

    // Master Data Routes (Dikembalikan ke sistem resource controller)
    Route::prefix('master-data')->name('master-data.')->group(function () {
        Route::resource('companies', CompanyController::class);
        Route::resource('categories', CategoryController::class);
    });
});