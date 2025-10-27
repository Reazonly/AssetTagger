<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\AssetUser;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel; // Pastikan ini di-import
use Barryvdh\DomPDF\Facade\Pdf;     // Pastikan ini di-import
use Illuminate\Database\Eloquent\Builder;

// ASUMSI: Anda memiliki class Export ini di App\Exports\
use App\Exports\InventorySummaryExport; 
use App\Exports\TrackingReportExport; 

class ReportController extends Controller
{
    /**
     * Helper Function untuk mendapatkan query Laporan Inventaris
     */
    private function getInventoryQuery(Request $request)
    {
        $query = Asset::query()
            ->join('categories', 'assets.category_id', '=', 'categories.id')
            // Tambahkan join ke sub_categories
            ->leftJoin('sub_categories', 'assets.sub_category_id', '=', 'sub_categories.id') 
            ->select(
                'categories.name as category_name',
                // LOGIC BARU: Jika sub_category.name NULL, gunakan assets.nama_barang untuk display & grouping
                DB::raw('COALESCE(sub_categories.name, assets.nama_barang) as sub_category_display_name'), 
                'assets.kondisi',
                DB::raw('count(assets.id) as total_assets'),
                DB::raw('sum(assets.harga_total) as total_value')
            )
            // Group by display name baru
            ->groupBy('categories.name', 'sub_category_display_name', 'assets.kondisi') 
            ->orderBy('categories.name')
            ->orderBy('sub_category_display_name');

        if ($request->filled('category_id')) {
            $query->where('assets.category_id', $request->category_id);
        }
        if ($request->filled('sub_category_id')) {
            $query->where('assets.sub_category_id', $request->sub_category_id);
        }
        if ($request->filled('company_id')) {
            $query->where('assets.company_id', $request->company_id);
        }
        if ($request->filled('kondisi')) {
            $query->where('assets.kondisi', $request->kondisi);
        }
        if ($request->filled('asset_user_id')) {
             $query->where('assets.asset_user_id', $request->asset_user_id);
        }
        
        return $query;
    }

    /**
     * Helper Function untuk mendapatkan query Laporan Tracking
     */
    protected function getTrackingQuery(Request $request): Builder
    {
        $query = Asset::query()
            ->with(['category', 'subCategory', 'company', 'assetUser'])
            // HANYA ambil aset yang sedang dialokasikan
            ->whereNotNull('asset_user_id'); 

        // Terapkan Filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->sub_category_id);
        }
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->filled('asset_user_id')) {
            $query->where('asset_user_id', $request->asset_user_id);
        }
        
        // Filter Lokasi (menggunakan nama input 'location' dari Blade)
        if ($request->filled('location')) {
            $query->where('lokasi', $request->location);
        }
        
        // Filter Pencarian Cepat
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function (Builder $q) use ($search) {
                $q->where('code_asset', 'like', '%' . $search . '%')
                  ->orWhere('nama_barang', 'like', '%' . $search . '%')
                  ->orWhere('serial_number', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }
    // =========================================================================
    // VIEW REPORT METHODS
    // =========================================================================

    public function inventoryReport(Request $request)
    {

        // 1. Ambil data untuk dropdown filter
        $categories = Category::orderBy('name')->get();
        $subCategories = SubCategory::with('category')->orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        $assetUsers = AssetUser::orderBy('nama')->get();

        // Query utama untuk mengambil SEMUA ASET yang sesuai filter
        $query = Asset::query()->with(['category', 'subCategory', 'company', 'assetUser']);
        $allUniqueSpecValues = [];
        $selectedFilters = $request->all();

        // --- A. PENGUMPULAN DATA UNIK SPESIFIKASI (Untuk Dropdown Dinamis) ---
        // Ambil data spesifikasi dari SEMUA aset untuk mengisi dropdown
        $allAssetsForSpecs = Asset::select('sub_category_id', 'specifications')->get(); 

        foreach ($allAssetsForSpecs as $asset) {
            // Perbaikan Bug: Menangani kasus di mana 'specifications' sudah di-cast ke array
            $specs = is_string($asset->specifications) 
                     ? json_decode($asset->specifications, true) 
                     : $asset->specifications;
            
            // Lanjutkan hanya jika $specs adalah array valid dan sub_category_id ada
            if (!is_array($specs) || !$asset->sub_category_id) {
                continue; 
            }

            if (!isset($allUniqueSpecValues[$asset->sub_category_id])) {
                $allUniqueSpecValues[$asset->sub_category_id] = [];
            }
            
            foreach ($specs as $key => $value) {
                $formKey = $key; // Gunakan key apa adanya

                if (!isset($allUniqueSpecValues[$asset->sub_category_id][$formKey])) {
                    $allUniqueSpecValues[$asset->sub_category_id][$formKey] = collect();
                }
                
                // Tambahkan nilai unik jika tidak NULL/Kosong
                if (!is_null($value) && $value !== '') {
                    $allUniqueSpecValues[$asset->sub_category_id][$formKey]->push($value);
                }
            }
        }

        // Bersihkan duplikasi dan urutkan
        foreach ($allUniqueSpecValues as $subCatId => $specKeys) {
            foreach ($specKeys as $specKey => $valuesCollection) {
                // Konversi Collection kembali ke array setelah unique dan sort
                $allUniqueSpecValues[$subCatId][$specKey] = $valuesCollection->unique()->sort()->values()->toArray();
            }
        }
        // --- END: PENGUMPULAN DATA SPESIFIKASI ---


        
        // --- B. PENERAPAN FILTER PADA QUERY UTAMA ---
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->sub_category_id);
        }
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->filled('asset_user_id')) {
            $query->where('asset_user_id', $request->asset_user_id);
        }

        // Filter Spesifikasi (Perbaikan JSON Exact Match)
        if ($request->filled('spec_key') && $request->filled('spec_value')) {
            $key = $request->input('spec_key');
            $value = $request->input('spec_value');
            $jsonColumn = 'specifications'; // NAMA KOLOM JSON ASLI ANDA

            // Menggunakan whereRaw dengan binding untuk keamanan dan pencocokan eksak
            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT({$jsonColumn}, '$.\"{$key}\"')) = ?", [$value]);
        }
        
        // 3. Ambil data aset yang sudah difilter
        $filteredAssets = $query->get();

        // 4. Buat data rekapitulasi (summary) dari hasil query
        $inventorySummary = $filteredAssets->groupBy(function($asset) {
            // Kunci unik untuk rekapitulasi: Kategori|SubKategori|PerusahaanAset|Kondisi
            $categoryName = optional($asset->category)->name ?? 'Tanpa Kategori';
            $subCategoryDisplayName = optional($asset->subCategory)->name ?? $asset->nama_barang;
            $companyName = optional($asset->company)->name ?? 'Tanpa Perusahaan';
            return $categoryName . '|' . $subCategoryDisplayName . '|' . $companyName . '|' . $asset->kondisi;
        })->map(function ($groupedAssets, $key) {
            $firstAsset = $groupedAssets->first();
            $totalHarga = $groupedAssets->sum('harga_total');

            return [
                'category_name' => optional($firstAsset->category)->name ?? 'Tanpa Kategori',
                'sub_category_display_name' => optional($firstAsset->subCategory)->name ?? $firstAsset->nama_barang,
                'company_name' => optional($firstAsset->company)->name ?? 'Tanpa Perusahaan',
                'kondisi' => $firstAsset->kondisi,
                'count' => $groupedAssets->count(), 
                'total_harga' => $totalHarga, 
                'details' => $groupedAssets, 
            ];
        })->sortBy('sub_category_display_name');
        // Urutkan berdasarkan nama sub kategori

        // 5. Kirim data ke view
        return view('reports.inventory', compact(
            'inventorySummary', 
            'categories', 
            'subCategories', 
            'companies', 
            'assetUsers', 
            'allUniqueSpecValues', 
            'selectedFilters'
        ));
    

    }
    public function trackingReport(Request $request)
    {
        // 1. Ambil data master untuk filter
        $categories = Category::orderBy('name')->get();
        $subCategories = SubCategory::orderBy('category_id')->orderBy('name')->get(); 
        $assetUsers = AssetUser::orderBy('nama')->get();
        $companies = Company::orderBy('name')->get();

        // 2. Ambil daftar lokasi unik untuk filter Lokasi
        $locations = Asset::select('lokasi')
                            ->distinct()
                            ->whereNotNull('lokasi')
                            ->pluck('lokasi')
                            ->sort()
                            ->values()
                            ->toArray();

        // 3. Ambil data alokasi saat ini menggunakan helper method
        $currentAllocations = $this->getTrackingQuery($request)->paginate(20);

        // 4. Kirim semua variabel ke view
        return view('reports.tracking', [ 
            'assets' => $currentAllocations,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'assetUsers' => $assetUsers,
            'companies' => $companies,
            'locations' => $locations,
            'selectedFilters' => $request->all(),
        ]);
    }
    // =========================================================================
    // EXPORT METHODS
    // =========================================================================

    /**
     * Ekspor Laporan Inventaris ke Excel
     */
    public function exportInventoryExcel(Request $request)
    {
        $inventorySummary = $this->getInventoryQuery($request)->get();
        if ($inventorySummary->isEmpty()) {
             return redirect()->route('reports.inventory')->with('error', 'Tidak ada data inventaris yang sesuai untuk diexport.');
        }

        // ASUMSI: InventorySummaryExport menerima Collection data hasil query
        return Excel::download(new InventorySummaryExport($inventorySummary), 'laporan_inventaris_' . date('Ymd_His') . '.xlsx');
    }

    /**
     * Ekspor Laporan Inventaris ke PDF
     */
    public function exportInventoryPDF(Request $request)
    {
        $inventorySummary = $this->getInventoryQuery($request)->get();
        if ($inventorySummary->isEmpty()) {
             return redirect()->route('reports.inventory')->with('error', 'Tidak ada data inventaris yang sesuai untuk dicetak.');
        }

        // ASUMSI: View PDF berada di resources/views/reports/pdf-inventory.blade.php
        $pdf = Pdf::loadView('reports.pdf-inventory', [
            'inventorySummary' => $inventorySummary,
            'filters' => $request->all(),
        ]);
        
        return $pdf->download('laporan_inventaris_' . date('Ymd_His') . '.pdf');
    }
    
    /**
     * Ekspor Laporan Pelacakan ke Excel
     */
    public function exportTrackingExcel(Request $request)
    {
        $currentAllocations = $this->getTrackingQuery($request)->get();
         if ($currentAllocations->isEmpty()) {
             return redirect()->route('reports.tracking')->with('error', 'Tidak ada data alokasi yang sesuai untuk diexport.');
        }

        // ASUMSI: TrackingReportExport menerima Collection data hasil query
        return Excel::download(new TrackingReportExport($currentAllocations), 'laporan_alokasi_' . date('Ymd_His') . '.xlsx');
    }

    /**
     * Ekspor Laporan Pelacakan ke PDF
     */
    public function exportTrackingPDF(Request $request)
    {
        $currentAllocations = $this->getTrackingQuery($request)->get();
         if ($currentAllocations->isEmpty()) {
             return redirect()->route('reports.tracking')->with('error', 'Tidak ada data alokasi yang sesuai untuk dicetak.');
        }

        // ASUMSI: View PDF berada di resources/views/reports/pdf-tracking.blade.php
        $pdf = Pdf::loadView('reports.pdf-tracking', [
            'currentAllocations' => $currentAllocations,
            'filters' => $request->all(),
        ]);
        
        return $pdf->download('laporan_alokasi_' . date('Ymd_His') . '.pdf');
    }
}