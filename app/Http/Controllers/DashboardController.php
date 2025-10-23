<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // <-- Pastikan ini ditambahkan

class DashboardController extends Controller
{
    public function index()
    {
        try {
            
            // --- AWAL MODIFIKASI FILTER ---
            $user = Auth::user();
            
            // 1. Buat query dasar
            $assetQuery = Asset::query();
            $companyQuery = Company::query();

            // 2. Buat closure filter (kosong untuk super-admin)
            $filterClosure = function ($query) {}; 

            // 3. Jika BUKAN super-admin, terapkan filter
            if (!$user->hasRole('super-admin')) {
                // Dapatkan ID perusahaan yang boleh diakses user
                // --- PERBAIKAN DI BARIS BERIKUT ---
                $allowedCompanyIds = $user->companies()->pluck('companies.id'); // <-- Perbaikan pluck di sini
                // --- AKHIR PERBAIKAN ---
                
                // Terapkan filter ke query dasar
                $assetQuery->whereIn('company_id', $allowedCompanyIds);
                $companyQuery->whereIn('id', $allowedCompanyIds);

                // Buat closure untuk filter relasi (withCount)
                $filterClosure = function ($query) use ($allowedCompanyIds) {
                    $query->whereIn('company_id', $allowedCompanyIds);
                };
            }
            // --- AKHIR MODIFIKASI FILTER ---


            // 4. Gunakan query yang sudah difilter (clone agar tidak bentrok)
            $totalAssets = (clone $assetQuery)->count();
            $assetsBaik = (clone $assetQuery)->where('kondisi', 'Baik')->count();
            $assetsRusak = (clone $assetQuery)->where('kondisi', 'Rusak')->count();
            $assetsPerbaikan = (clone $assetQuery)->where('kondisi', 'Perbaikan')->count();
            $totalNilaiAset = (clone $assetQuery)->sum('harga_total');

            $labels = [];
            $dataMasuk = [];
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $labels[] = $month->format('M Y');
                
                // Terapkan filter ke data chart
                $dataMasuk[] = (clone $assetQuery) // Gunakan $assetQuery
                                    ->whereYear('created_at', $month->year)
                                    ->whereMonth('created_at', $month->month)
                                    ->count();
            }

            // Terapkan filter closure ke withCount
            $assetsByCategory = Category::withCount(['assets' => $filterClosure])
                ->having('assets_count', '>', 0)
                ->orderBy('assets_count', 'desc')
                ->get();
            
            // Terapkan filter closure ke withCount dan $companyQuery
            $assetsByCompany = (clone $companyQuery) // Gunakan $companyQuery
                ->withCount(['assets' => $filterClosure])
                ->having('assets_count', '>', 0)
                ->orderBy('assets_count', 'desc')
                ->get();

            // Gunakan $assetQuery untuk aset terbaru
            $recentAssets = (clone $assetQuery)->with(['category', 'assetUser'])->latest()->take(5)->get();

            return view('Dashboard.index', compact(
                'totalAssets', 'assetsBaik', 'assetsRusak', 'assetsPerbaikan', 'totalNilaiAset',
                'labels', 'dataMasuk',
                'assetsByCategory', 'assetsByCompany',
                'recentAssets'
            ));

        } catch (\Exception $e) {
            // Tampilkan pesan error jika ada masalah
            Log::error("Dashboard Error: " . $e->getMessage()); // Log error untuk debugging
            return view('Dashboard.index', [
                'dashboardError' => 'Gagal memuat data dashboard: ' . $e->getMessage() 
            ]);
        }
    }
}

