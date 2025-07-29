<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController; // <-- Tambahkan ini

// Rute untuk halaman login & registrasi (bisa diakses oleh tamu)
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register'); // <-- Rute baru
Route::post('register', [RegisterController::class, 'register']); // <-- Rute baru

// Rute untuk logout (hanya bisa diakses setelah login)
Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Halaman utama akan mengarahkan ke halaman aset jika sudah login, atau ke login jika belum
Route::get('/', function () {
    return redirect()->route('assets.index');
});

// Grup rute yang dilindungi (hanya bisa diakses setelah login)
Route::middleware('auth')->group(function () {
    Route::post('assets/import', [AssetController::class, 'import'])->name('assets.import');
    Route::get('assets/print', [AssetController::class, 'print'])->name('assets.print');
    Route::resource('assets', AssetController::class);
});

// Rute publik untuk QR Code (tidak perlu login)
Route::get('assets/{asset}/public', [AssetController::class, 'publicShow'])->name('assets.public.show');