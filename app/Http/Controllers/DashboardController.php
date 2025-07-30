<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Anda bisa menambahkan logika di sini nanti, contohnya:
        $totalAssets = Asset::count();
        $totalUsers = User::count();
        $assetsRusak = Asset::where('kondisi', 'RUSAK')->count();

        return view('dashboard.index', [
            'totalAssets' => $totalAssets,
            'totalUsers' => $totalUsers,
            'assetsRusak' => $assetsRusak,
        ]);
    }
}