<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard dengan ringkasan data aset.
     */
    public function index()
    {
        try {
            // Menghitung total semua aset
            $totalAssets = Asset::count();

            // Menghitung aset dengan kondisi 'Baik'
            $assetsBaik = Asset::where('kondisi', 'Baik')->count();

            // Menghitung aset dengan kondisi 'Rusak'
            $assetsRusak = Asset::where('kondisi', 'Rusak')->count();

            // --- LOGIKA UNTUK DATA GRAFIK ---
            $labels = [];
            $dataMasuk = [];
            $dataKeluar = [];

            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                
                // Menggunakan format yang lebih aman: "Jul 2025"
                $labels[] = $month->format('M Y');

                // Query data aset masuk
                $dataMasuk[] = Asset::whereYear('created_at', $month->year)
                                    ->whereMonth('created_at', $month->month)
                                    ->count();

                // Query data aset keluar
                $dataKeluar[] = Asset::onlyTrashed()
                                     ->whereYear('deleted_at', $month->year)
                                     ->whereMonth('deleted_at', $month->month)
                                     ->count();
            }

            return view('Dashboard.index', compact(
                'totalAssets',
                'assetsBaik',
                'assetsRusak',
                'labels',
                'dataMasuk',
                'dataKeluar'
            ));

        } catch (\Exception $e) {
            // Jika terjadi error, kirim data kosong dan pesan error ke view
            // untuk mencegah halaman rusak.
            return view('Dashboard.index', [
                'totalAssets' => 0,
                'assetsBaik' => 0,
                'assetsRusak' => 0,
                'labels' => [],
                'dataMasuk' => [],
                'dataKeluar' => [],
                'dashboardError' => 'Gagal memuat data dashboard: ' . $e->getMessage()
            ]);
        }
    }
}
