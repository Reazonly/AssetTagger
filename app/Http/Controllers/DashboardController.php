<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $totalAssets = Asset::count();
            $assetsBaik = Asset::where('kondisi', 'Baik')->count();
            $assetsRusak = Asset::where('kondisi', 'Rusak')->count();

            $labels = [];
            $dataMasuk = [];
            $dataKeluar = [];

            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $labels[] = $month->format('M Y');
                $dataMasuk[] = Asset::whereYear('created_at', $month->year)
                                    ->whereMonth('created_at', $month->month)
                                    ->count();
                $dataKeluar[] = Asset::onlyTrashed()
                                     ->whereYear('deleted_at', $month->year)
                                     ->whereMonth('deleted_at', $month->month)
                                     ->count();
            }

            return view('Dashboard.index', compact(
                'totalAssets', 'assetsBaik', 'assetsRusak',
                'labels', 'dataMasuk', 'dataKeluar'
            ));

        } catch (\Exception $e) {
            return view('Dashboard.index', [
                'totalAssets' => 0, 'assetsBaik' => 0, 'assetsRusak' => 0,
                'labels' => [], 'dataMasuk' => [], 'dataKeluar' => [],
                'dashboardError' => 'Gagal memuat data dashboard: ' . $e->getMessage()
            ]);
        }
    }
}