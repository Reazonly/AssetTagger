<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;

Route::get('/', function () {
    return redirect()->route('assets.index');
});

// Rute untuk fungsionalitas khusus
Route::post('assets/import', [AssetController::class, 'import'])->name('assets.import');
Route::get('assets/print', [AssetController::class, 'print'])->name('assets.print');
Route::get('assets/{asset}/public', [AssetController::class, 'publicShow'])->name('assets.public.show');

// Rute Resource untuk operasi CRUD standar
Route::resource('assets', AssetController::class);
