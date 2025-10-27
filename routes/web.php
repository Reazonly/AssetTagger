<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\Auth\LoginController;
// use App\Http\Controllers\Auth\RegisterController; // Tidak digunakan
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
Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Public Routes
Route::get('assets/{asset}/public', [AssetController::class, 'publicShow'])->name('assets.public.show');
Route::get('assets/{asset}/pdf', [AssetController::class, 'downloadPDF'])->name('assets.pdf');
// Route::get('/assets/get-units/{category}', [AssetController::class, 'getUnits'])->name('assets.getUnits'); // Sepertinya tidak digunakan lagi

// Main application routes (protected by auth middleware)
Route::middleware('auth')->group(function () {
    // Dashboard 
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:view-dashboard');

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // Reports (Dengan Permission)
    Route::prefix('reports')->group(function () {
        Route::get('inventory', [ReportController::class, 'inventoryReport'])->name('reports.inventory')->middleware('permission:reports-view-inventory');
        Route::get('tracking', [ReportController::class, 'trackingReport'])->name('reports.tracking')->middleware('permission:reports-view-tracking');
        Route::get('inventory/export/excel', [ReportController::class, 'exportInventoryExcel'])->name('reports.inventory.excel')->middleware('permission:reports-export-inventory');
        Route::get('inventory/export/pdf', [ReportController::class, 'exportInventoryPDF'])->name('reports.inventory.pdf')->middleware('permission:reports-export-inventory');
        Route::get('tracking/export/excel', [ReportController::class, 'exportTrackingExcel'])->name('reports.tracking.excel')->middleware('permission:reports-export-tracking');
        Route::get('tracking/export/pdf', [ReportController::class, 'exportTrackingPDF'])->name('reports.tracking.pdf')->middleware('permission:reports-export-tracking');
    });


    // Assets (Dengan Permission)
    Route::prefix('assets')->name('assets.')->group(function() {
        Route::get('/', [AssetController::class, 'index'])->name('index')->middleware('permission:view-asset');
        Route::get('/create', [AssetController::class, 'create'])->name('create')->middleware('permission:create-asset');
        Route::get('/print', [AssetController::class, 'print'])->name('print')->middleware('permission:print-asset'); // Lebih spesifik
        Route::get('/export', [AssetController::class, 'export'])->name('export')->middleware('permission:export-asset'); // Lebih spesifik
        Route::post('/', [AssetController::class, 'store'])->name('store')->middleware('permission:create-asset');
        Route::post('/import', [AssetController::class, 'import'])->name('import')->middleware('permission:import-asset');
        Route::get('/{asset}', [AssetController::class, 'show'])->name('show')->middleware('permission:view-asset');
        Route::get('/{asset}/edit', [AssetController::class, 'edit'])->name('edit')->middleware('permission:edit-asset');
        Route::put('/{asset}', [AssetController::class, 'update'])->name('update')->middleware('permission:edit-asset');
        Route::delete('/{asset}', [AssetController::class, 'destroy'])->name('destroy')->middleware('permission:delete-asset');
    });

    // --- MANAJEMEN PENGGUNA LOGIN (USER & ROLE) ---
    Route::prefix('users')->name('users.')->middleware('permission:view-user')->group(function() {
        Route::get('/', [UserController::class, 'index'])->name('index');
        
        // --- PINDAHKAN CREATE & STORE KE LUAR GRUP SUPERADMIN ---
        Route::get('/create', [UserController::class, 'create'])->name('create')->middleware('permission:manage-roles'); // atau permission:create-user
        Route::post('/', [UserController::class, 'store'])->name('store')->middleware('permission:manage-roles'); // atau permission:create-user
        // --- AKHIR PEMINDAHAN ---

        Route::post('/{user}/update-access', [UserController::class, 'updateAccess'])->name('update-access')->middleware('permission:assign-role'); 

        // Hanya Super Admin yang bisa menghapus dan reset password
        Route::middleware('superadmin')->group(function () {
            // Route::get('/create', [UserController::class, 'create'])->name('create'); // Sudah dipindah
            // Route::post('/', [UserController::class, 'store'])->name('store'); // Sudah dipindah
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('resetPassword');
        });
    });

    // Grup untuk Master Data (Dengan Permission)
    Route::prefix('master-data')->name('master-data.')->middleware('permission:manage-master-data')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('companies', CompanyController::class)->except(['show']);
        Route::resource('asset-users', AssetUserController::class)->except(['show']); // Pastikan ini tidak dikomentari

        // Sub-Categories (Pastikan route konsisten)
        Route::get('sub-categories', [SubCategoryController::class, 'index'])->name('sub-categories.index');
        Route::get('sub-categories/category/{category}', [SubCategoryController::class, 'show'])->name('sub-categories.show'); // Tambah 'category/' agar jelas
        Route::get('sub-categories/category/{category}/create', [SubCategoryController::class, 'create'])->name('sub-categories.create');
        Route::post('sub-categories/category/{category}', [SubCategoryController::class, 'store'])->name('sub-categories.store');
        Route::get('sub-categories/{subCategory}/edit', [SubCategoryController::class, 'edit'])->name('sub-categories.edit'); // Parameter {subCategory}
        Route::put('sub-categories/{subCategory}', [SubCategoryController::class, 'update'])->name('sub-categories.update'); // Parameter {subCategory}
        Route::delete('sub-categories/{subCategory}', [SubCategoryController::class, 'destroy'])->name('sub-categories.destroy'); // Parameter {subCategory}
   
        // Import Routes
        Route::post('categories/import', [CategoryController::class, 'import'])->name('categories.import'); 
        Route::post('asset-users/import', [AssetUserController::class, 'import'])->name('asset-users.import'); 
        Route::post('sub-categories/category/{category}/import', [SubCategoryController::class, 'import'])->name('sub-categories.import'); 
    });
    
    // --- MANAJEMEN ROLES & PERMISSIONS (Dengan Permission) ---
    Route::prefix('roles-management')->name('roles.')->middleware('permission:manage-roles')->group(function() { // Ganti middleware
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });
});

