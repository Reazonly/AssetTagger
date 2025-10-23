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
use App\Http\Controllers\AssetUserController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ReportController;

// Authentication Routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
// Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
// Route::post('register', [RegisterController::class, 'register']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Public Routes
Route::get('assets/{asset}/public', [AssetController::class, 'publicShow'])->name('assets.public.show');
Route::get('assets/{asset}/pdf', [AssetController::class, 'downloadPDF'])->name('assets.pdf');
Route::get('/assets/get-units/{category}', [AssetController::class, 'getUnits'])->name('assets.getUnits');

// Main application routes (protected by auth middleware)
Route::middleware('auth')->group(function () {
    // Dashboard (membutuhkan izin 'view-dashboard')
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:view-dashboard');

    // Profil (bisa diakses semua pengguna yang login)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::prefix('reports')->group(function () {
        Route::get('inventory', [ReportController::class, 'inventoryReport'])->name('reports.inventory');
        Route::get('tracking', [ReportController::class, 'trackingReport'])->name('reports.tracking');

        // Export Routes
        Route::get('inventory/export/excel', [ReportController::class, 'exportInventoryExcel'])->name('reports.inventory.excel');
        Route::get('inventory/export/pdf', [ReportController::class, 'exportInventoryPDF'])->name('reports.inventory.pdf');
        Route::get('tracking/export/excel', [ReportController::class, 'exportTrackingExcel'])->name('reports.tracking.excel');
        Route::get('tracking/export/pdf', [ReportController::class, 'exportTrackingPDF'])->name('reports.tracking.pdf');
    });


    Route::prefix('assets')->name('assets.')->group(function() {
        // Rute GET (statis)
        Route::get('/', [AssetController::class, 'index'])->name('index')->middleware('permission:view-asset');
        Route::get('/create', [AssetController::class, 'create'])->name('create')->middleware('permission:create-asset');
        Route::get('/print', [AssetController::class, 'print'])->name('print')->middleware('permission:view-asset');
        Route::get('/export', [AssetController::class, 'export'])->name('export')->middleware('permission:view-asset');

        // Rute POST
        Route::post('/', [AssetController::class, 'store'])->name('store')->middleware('permission:create-asset');
        Route::post('/import', [AssetController::class, 'import'])->name('import')->middleware('permission:import-asset');

        // Rute GET (dinamis dengan parameter {asset})
        Route::get('/{asset}', [AssetController::class, 'show'])->name('show')->middleware('permission:view-asset');
        Route::get('/{asset}/edit', [AssetController::class, 'edit'])->name('edit')->middleware('permission:edit-asset');

        // Rute PUT & DELETE (dinamis)
        Route::put('/{asset}', [AssetController::class, 'update'])->name('update')->middleware('permission:edit-asset');
        Route::delete('/{asset}', [AssetController::class, 'destroy'])->name('destroy')->middleware('permission:delete-asset');
    });

    // --- RUTE ADMIN AREA DENGAN PERMISSION ---
    // Grup untuk semua yang terkait manajemen pengguna & role
    Route::prefix('users')->name('users.')->middleware('permission:view-user')->group(function() {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/{user}/assign-roles', [UserController::class, 'assignRoles'])->name('assign-roles')->middleware('permission:assign-role');
        Route::post('/{user}/assign-companies', [UserController::class, 'assignCompanies'])->name('assign-companies')->middleware('permission:assign-role'); 

        // Hanya Super Admin yang bisa membuat, menghapus, dan reset password
        Route::middleware('superadmin')->group(function () {
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('resetPassword');
        });
    });

    // Grup untuk Master Data
    Route::prefix('master-data')->name('master-data.')->middleware('permission:manage-master-data')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('companies', CompanyController::class)->except(['show']);
        Route::resource('asset-users', AssetUserController::class)->except(['show']);

        Route::get('sub-categories', [SubCategoryController::class, 'index'])->name('sub-categories.index');
        Route::get('sub-categories/{category}', [SubCategoryController::class, 'show'])->name('sub-categories.show');
        Route::get('sub-categories/{category}/create', [SubCategoryController::class, 'create'])->name('sub-categories.create');
        Route::post('sub-categories/{category}', [SubCategoryController::class, 'store'])->name('sub-categories.store');
        Route::get('sub-categories/{subCategory}/edit', [SubCategoryController::class, 'edit'])->name('sub-categories.edit');
        Route::put('sub-categories/{subCategory}', [SubCategoryController::class, 'update'])->name('sub-categories.update');
        Route::delete('sub-categories/{subCategory}', [SubCategoryController::class, 'destroy'])->name('sub-categories.destroy');
   
        Route::post('categories/import', [CategoryController::class, 'import'])->name('categories.import'); // <-- TAMBAHKAN INI
        Route::post('asset-users/import', [AssetUserController::class, 'import'])->name('asset-users.import'); 
        Route::post('sub-categories/{category}/import', [SubCategoryController::class, 'import'])->name('sub-categories.import'); // Diubah
    });
    
    // --- RUTE BARU UNTUK MANAJEMEN ROLES & PERMISSIONS ---
    Route::prefix('roles-management')->name('roles.')->middleware('superadmin')->group(function() {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });
});
    