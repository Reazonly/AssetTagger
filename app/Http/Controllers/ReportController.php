<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\AssetUser;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel; 
use Barryvdh\DomPDF\Facade\Pdf;     
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth; // <-- Pastikan ini ditambahkan

// ASUMSI: Anda memiliki class Export ini di App\Exports\
use App\Exports\InventorySummaryExport; 
use App\Exports\TrackingReportExport; 

class ReportController extends Controller
{
    /**
     * Helper Function untuk mendapatkan query Laporan Inventaris
     */
    private function getInventoryQuery(Request $request): Builder // Tipe return ditambahkan
    {
        $query = Asset::query()
            ->join('categories', 'assets.category_id', '=', 'categories.id')
            ->leftJoin('sub_categories', 'assets.sub_category_id', '=', 'sub_categories.id') 
            ->select(
                'categories.name as category_name',
                DB::raw('COALESCE(sub_categories.name, assets.nama_barang) as sub_category_display_name'), 
                'assets.kondisi',
                DB::raw('count(assets.id) as total_assets'),
                DB::raw('sum(assets.harga_total) as total_value')
            )
            ->groupBy('categories.name', 'sub_category_display_name', 'assets.kondisi') 
            ->orderBy('categories.name')
            ->orderBy('sub_category_display_name');

        // --- TAMBAHKAN FILTER PERUSAHAAN DI SINI ---
        $user = Auth::user();
        if (!$user->hasRole('super-admin')) {
            $allowedCompanyIds = $user->companies()->pluck('companies.id'); 
            // Gunakan 'assets.company_id' karena ada join
            $query->whereIn('assets.company_id', $allowedCompanyIds); 
        }
        // --- AKHIR FILTER PERUSAHAAN ---

        // Filter lain (biarkan seperti sebelumnya)
        if ($request->filled('category_id')) {
            $query->where('assets.category_id', $request->category_id);
        }
        if ($request->filled('sub_category_id')) {
            $query->where('assets.sub_category_id', $request->sub_category_id);
        }
        // Filter company_id dari request (jika user memilih filter spesifik)
        // Filter ini akan berjalan SETELAH filter hak akses di atas
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
            ->with(['category', 'subCategory', 'company', 'assetUser.company']) // Eager load company dari assetUser
            ->whereNotNull('asset_user_id'); 

        // --- TAMBAHKAN FILTER PERUSAHAAN DI SINI ---
        $user = Auth::user();
        if (!$user->hasRole('super-admin')) {
            $allowedCompanyIds = $user->companies()->pluck('companies.id');
            // Filter berdasarkan company_id milik ASET itu sendiri
            $query->whereIn('company_id', $allowedCompanyIds); 
        }
        // --- AKHIR FILTER PERUSAHAAN ---

        // Terapkan Filter lain (biarkan seperti sebelumnya)
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->sub_category_id);
        }
        // Filter company_id dari request (jika user memilih filter spesifik)
        // Filter ini akan berjalan SETELAH filter hak akses di atas
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->filled('asset_user_id')) {
            $query->where('asset_user_id', $request->asset_user_id);
        }
        if ($request->filled('location')) {
            $query->where('lokasi', $request->location);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function (Builder $q) use ($search) {
                $q->where('code_asset', 'like', '%' . $search . '%')
                  ->orWhere('nama_barang', 'like', '%' . $search . '%')
                  ->orWhere('serial_number', 'like', '%' . $search . '%')
                  // Tambahkan pencarian berdasarkan nama pengguna aset jika perlu
                  ->orWhereHas('assetUser', function($uq) use ($search){
                        $uq->where('nama', 'like', '%' . $search . '%');
                  });
            });
        }

        return $query;
    }
    // =========================================================================
    // VIEW REPORT METHODS (Tidak perlu diubah signifikan, karena filter ada di helper)
    // =========================================================================

    public function inventoryReport(Request $request)
    {
        // Ambil data untuk dropdown filter
        $categories = Category::orderBy('name')->get();
        $subCategories = SubCategory::with('category')->orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        $assetUsers = AssetUser::orderBy('nama')->get();
        $selectedFilters = $request->all(); // Simpan filter terpilih

        // --- PENGUMPULAN DATA SPESIFIKASI UNIK (Biarkan seperti sebelumnya) ---
        $allUniqueSpecValues = [];
        $allAssetsForSpecs = Asset::select('sub_category_id', 'specifications')->get(); 
        foreach ($allAssetsForSpecs as $asset) {
            $specs = is_string($asset->specifications) 
                     ? json_decode($asset->specifications, true) 
                     : $asset->specifications;
            if (!is_array($specs) || !$asset->sub_category_id) continue; 
            if (!isset($allUniqueSpecValues[$asset->sub_category_id])) {
                $allUniqueSpecValues[$asset->sub_category_id] = [];
            }
            foreach ($specs as $key => $value) {
                $formKey = $key; 
                if (!isset($allUniqueSpecValues[$asset->sub_category_id][$formKey])) {
                    $allUniqueSpecValues[$asset->sub_category_id][$formKey] = collect();
                }
                if (!is_null($value) && $value !== '') {
                    $allUniqueSpecValues[$asset->sub_category_id][$formKey]->push($value);
                }
            }
        }
        foreach ($allUniqueSpecValues as $subCatId => $specKeys) {
            foreach ($specKeys as $specKey => $valuesCollection) {
                $allUniqueSpecValues[$subCatId][$specKey] = $valuesCollection->unique()->sort()->values()->toArray();
            }
        }
        // --- END: PENGUMPULAN DATA SPESIFIKASI ---

        // --- DAPATKAN QUERY UTAMA DARI HELPER (Sudah termasuk filter hak akses & request) ---
        $inventoryQuery = $this->getInventoryQuery($request);
        // --- END DAPATKAN QUERY ---

        // Eksekusi query untuk mendapatkan summary
        $inventorySummary = $inventoryQuery->get()
            ->groupBy(function($item) {
                // Grouping berdasarkan kombinasi unik untuk rekap
                return $item->category_name . '|' . $item->sub_category_display_name . '|' . $item->kondisi; 
            })
            ->map(function ($groupedItems, $key) {
                $firstItem = $groupedItems->first();
                $totalHarga = $groupedItems->sum('total_value'); // Gunakan total_value dari query
                $totalAssets = $groupedItems->sum('total_assets'); // Gunakan total_assets dari query
                
                // Ambil ID detail aset (jika diperlukan untuk fitur expand)
                // Ini mungkin perlu query tambahan jika tidak diambil di getInventoryQuery
                // Untuk sekarang kita fokus pada summary
                $assetIds = []; // Placeholder

                return [
                    'category_name' => $firstItem->category_name,
                    'sub_category_display_name' => $firstItem->sub_category_display_name,
                    // 'company_name' => $firstItem->company_name, // Tidak ada di select awal, perlu ditambahkan jika mau
                    'kondisi' => $firstItem->kondisi,
                    'count' => $totalAssets, 
                    'total_harga' => $totalHarga, 
                    'details_ids' => $assetIds, // Placeholder ID detail
                ];
            })
            ->sortBy('sub_category_display_name');

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
        // Ambil data master untuk filter
        $categories = Category::orderBy('name')->get();
        $subCategories = SubCategory::orderBy('category_id')->orderBy('name')->get(); 
        $assetUsers = AssetUser::orderBy('nama')->get();
        $companies = Company::orderBy('name')->get();
        $locations = Asset::select('lokasi')->distinct()->whereNotNull('lokasi')->pluck('lokasi')->sort()->values()->toArray();

        // --- DAPATKAN QUERY DARI HELPER (Sudah termasuk filter hak akses & request) ---
        $trackingQuery = $this->getTrackingQuery($request);
        // --- END DAPATKAN QUERY ---

        // Eksekusi query dengan paginasi
        $currentAllocations = $trackingQuery->paginate(20)->withQueryString(); // Tambahkan withQueryString

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
    // EXPORT METHODS (Filter sudah otomatis diterapkan karena menggunakan helper)
    // =========================================================================

    public function exportInventoryExcel(Request $request)
    {
        $inventorySummary = $this->getInventoryQuery($request)->get(); // Ambil dari helper
        if ($inventorySummary->isEmpty()) {
             return redirect()->route('reports.inventory')->with('error', 'Tidak ada data inventaris yang sesuai untuk diexport.');
        }
        return Excel::download(new InventorySummaryExport($inventorySummary), 'laporan_inventaris_' . date('Ymd_His') . '.xlsx');
    }

    public function exportInventoryPDF(Request $request)
    {
        $inventorySummary = $this->getInventoryQuery($request)->get(); // Ambil dari helper
        if ($inventorySummary->isEmpty()) {
             return redirect()->route('reports.inventory')->with('error', 'Tidak ada data inventaris yang sesuai untuk dicetak.');
        }
        $pdf = Pdf::loadView('reports.pdf-inventory', [
            'inventorySummary' => $inventorySummary,
            'filters' => $request->all(), // Kirim filter untuk ditampilkan di PDF jika perlu
        ]);
        return $pdf->download('laporan_inventaris_' . date('Ymd_His') . '.pdf');
    }
    
    public function exportTrackingExcel(Request $request)
    {
        $currentAllocations = $this->getTrackingQuery($request)->get(); // Ambil dari helper
         if ($currentAllocations->isEmpty()) {
             return redirect()->route('reports.tracking')->with('error', 'Tidak ada data alokasi yang sesuai untuk diexport.');
        }
        return Excel::download(new TrackingReportExport($currentAllocations), 'laporan_alokasi_' . date('Ymd_His') . '.xlsx');
    }

    public function exportTrackingPDF(Request $request)
    {
        $currentAllocations = $this->getTrackingQuery($request)->get(); // Ambil dari helper
         if ($currentAllocations->isEmpty()) {
             return redirect()->route('reports.tracking')->with('error', 'Tidak ada data alokasi yang sesuai untuk dicetak.');
        }
        $pdf = Pdf::loadView('reports.pdf-tracking', [
            'currentAllocations' => $currentAllocations,
            'filters' => $request->all(), // Kirim filter untuk ditampilkan di PDF jika perlu
        ]);
        return $pdf->download('laporan_alokasi_' . date('Ymd_His') . '.pdf');
    }
}
