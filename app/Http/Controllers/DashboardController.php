<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $totalAssets = Asset::count();
            $assetsBaik = Asset::where('kondisi', 'Baik')->count();
            $assetsRusak = Asset::where('kondisi', 'Rusak')->count();
            $assetsPerbaikan = Asset::where('kondisi', 'Perbaikan')->count();
            $totalNilaiAset = Asset::sum('harga_total');

            $labels = [];
            $dataMasuk = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $labels[] = $month->format('M Y');
                $dataMasuk[] = Asset::whereYear('created_at', $month->year)
                                    ->whereMonth('created_at', $month->month)
                                    ->count();
            }

            $assetsByCategory = Category::withCount('assets')
                ->having('assets_count', '>', 0)
                ->orderBy('assets_count', 'desc')
                ->get();
            
            $assetsByCompany = Company::withCount('assets')
                ->having('assets_count', '>', 0)
                ->orderBy('assets_count', 'desc')
                ->get();

            $recentAssets = Asset::with(['category', 'assetUser'])->latest()->take(5)->get();

            return view('Dashboard.index', compact(
                'totalAssets', 'assetsBaik', 'assetsRusak', 'assetsPerbaikan', 'totalNilaiAset',
                'labels', 'dataMasuk',
                'assetsByCategory', 'assetsByCompany',
                'recentAssets'
            ));

        } catch (\Exception $e) {
            return view('Dashboard.index', [
                'dashboardError' => 'Gagal memuat data dashboard: ' . $e->getMessage()
            ]);
        }
    }
}
