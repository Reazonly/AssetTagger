<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\Auth\LoginController;
// Hapus atau comment use RegisterController jika tidak dipakai
// use App\Http\Controllers\Auth\RegisterController; 
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
// Route::get('/assets/get-units/{category}', [AssetController::class, 'getUnits'])->name('assets.getUnits'); // Comment / Hapus jika tidak dipakai


// Main application routes (protected by auth middleware)
Route::middleware('auth')->group(function () {
    // Dashboard (membutuhkan izin 'view-dashboard')
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:view-dashboard');

    // Profil (bisa diakses semua pengguna yang login)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // --- GRUP LAPORAN DENGAN PERMISSION ---
    // Di sinilah pembatasan untuk laporan didefinisikan
    Route::prefix('reports')->name('reports.')->group(function () {
        // Halaman Laporan Inventaris (Memerlukan permission 'reports-view-inventory')
        Route::get('inventory', [ReportController::class, 'inventoryReport'])
             ->name('inventory')
             ->middleware('permission:reports-view-inventory'); // <-- Pembatasan lihat inventaris

        // Halaman Laporan Pelacakan (Memerlukan permission 'reports-view-tracking')
        Route::get('tracking', [ReportController::class, 'trackingReport'])
             ->name('tracking')
             ->middleware('permission:reports-view-tracking'); // <-- Pembatasan lihat pelacakan

        // Export Routes (Gunakan permission export yang sesuai)
        Route::get('inventory/export/excel', [ReportController::class, 'exportInventoryExcel'])
             ->name('inventory.excel')
             ->middleware('permission:reports-export-inventory'); // <-- Pembatasan ekspor inventaris

        Route::get('inventory/export/pdf', [ReportController::class, 'exportInventoryPDF'])
             ->name('inventory.pdf')
             ->middleware('permission:reports-export-inventory'); // <-- Pembatasan ekspor inventaris

        Route::get('tracking/export/excel', [ReportController::class, 'exportTrackingExcel'])
             ->name('tracking.excel')
             ->middleware('permission:reports-export-tracking'); // <-- Pembatasan ekspor pelacakan

        Route::get('tracking/export/pdf', [ReportController::class, 'exportTrackingPDF'])
             ->name('tracking.pdf')
             ->middleware('permission:reports-export-tracking'); // <-- Pembatasan ekspor pelacakan
    });
    // --- AKHIR GRUP LAPORAN ---


    Route::prefix('assets')->name('assets.')->group(function() {
        // Rute GET (statis)
        Route::get('/', [AssetController::class, 'index'])->name('index')->middleware('permission:view-asset');
        Route::get('/create', [AssetController::class, 'create'])->name('create')->middleware('permission:create-asset');
        Route::get('/print', [AssetController::class, 'print'])->name('print')->middleware('permission:print-asset'); // Sesuaikan permission jika perlu
        Route::get('/export', [AssetController::class, 'export'])->name('export')->middleware('permission:export-asset'); // Sesuaikan permission

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

    // --- RUTE ADMIN AREA ---
    // Grup untuk semua yang terkait manajemen pengguna & role
    Route::prefix('users')->name('users.')->middleware('permission:view-user')->group(function() {
        Route::get('/', [UserController::class, 'index'])->name('index');
        
        // Route untuk update Role & Company Access
        Route::post('/{user}/update-access', [UserController::class, 'updateAccess'])
              ->name('update-access')
              ->middleware('permission:assign-role'); // Gunakan permission yang sesuai

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
        // Pastikan baris ini TIDAK dikomentari jika Anda ingin route asset-users aktif
        Route::resource('asset-users', AssetUserController::class)->except(['show']); 

        // Sub Kategori
        Route::get('sub-categories', [SubCategoryController::class, 'index'])->name('sub-categories.index');
        Route::get('sub-categories/{category}', [SubCategoryController::class, 'show'])->name('sub-categories.show');
        Route::get('sub-categories/{category}/create', [SubCategoryController::class, 'create'])->name('sub-categories.create');
        Route::post('sub-categories/{category}', [SubCategoryController::class, 'store'])->name('sub-categories.store');
        Route::get('sub-categories/{subCategory}/edit', [SubCategoryController::class, 'edit'])->name('sub-categories.edit');
        Route::put('sub-categories/{subCategory}', [SubCategoryController::class, 'update'])->name('sub-categories.update');
        Route::delete('sub-categories/{subCategory}', [SubCategoryController::class, 'destroy'])->name('sub-categories.destroy');
        
        // Import untuk Master Data
        Route::post('categories/import', [CategoryController::class, 'import'])->name('categories.import'); 
        Route::post('asset-users/import', [AssetUserController::class, 'import'])->name('asset-users.import'); // Pastikan ini aktif jika perlu
        Route::post('sub-categories/{category}/import', [SubCategoryController::class, 'import'])->name('sub-categories.import');
    });
    
    // --- RUTE MANAJEMEN ROLES & PERMISSIONS ---
    // Gunakan permission 'manage-roles' BUKAN 'superadmin'
    Route::prefix('roles-management')->name('roles.')->middleware('permission:manage-roles')->group(function() { // <-- PASTIKAN MIDDLEWARE INI BENAR
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });
});

