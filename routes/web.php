<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;

Route::get('/', function () {
    return redirect()->route('assets.index');
});

Route::post('assets/import', [AssetController::class, 'import'])->name('assets.import');
Route::get('assets/print', [AssetController::class, 'print'])->name('assets.print');
Route::get('assets/{asset}/public', [App\Http\Controllers\AssetController::class, 'publicShow'])->name('assets.public.show');

// Ini akan otomatis membuat rute untuk index, create, store, show, edit, update, destroy
Route::resource('assets', AssetController::class);