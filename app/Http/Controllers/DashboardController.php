<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard dengan ringkasan data aset.
     */
    public function index()
    {
        // Menghitung total semua aset
        $totalAssets = Asset::count();

        // Menghitung aset dengan kondisi 'Baik'
        // Pastikan Anda memiliki kolom 'kondisi' di tabel 'assets' Anda.
        $assetsBaik = Asset::where('kondisi', 'Baik')->count();

        // Menghitung aset dengan kondisi 'Rusak'
        $assetsRusak = Asset::where('kondisi', 'Rusak')->count();

        // Mengirim data ke view dengan path yang benar
        // 'Dashboard.index' mengarah ke resources/views/Dashboard/index.blade.php
        return view('Dashboard.index', compact('totalAssets', 'assetsBaik', 'assetsRusak'));
    }
}
