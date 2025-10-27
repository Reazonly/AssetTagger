<?php

namespace App\Http\Controllers;

use App Models Asset;
use App Models Category;
use App Models SubCategory;
use App Models AssetUser;
use App Models Company;
use Illuminate<Http Request;
use Illuminate<Support Facades DB;
use Maatwebsite Excel Facades Excel;
use Barryvdh DomPDF Facade Pdf;
use Illuminate<Database Eloquent Builder;
use Illuminate<Support Facades Auth;
use Illuminate<Support Facades Log;

// ASUMSI: Anda memiliki class Export ini di App Exports
use App Exports InventorySummaryExport;
use App Exports TrackingReportExport;

class ReportController extends Controller
{
    /**
     * Helper Function untuk mendapatkan query Laporan Inventaris (Query Summary)
     */
    private function getInventorySummaryQuery(Request $request): Builder
    {
        $query = Asset::query()
            ->join('categories', 'assets.category_id', '=', 'categories.id')
            ->leftJoin('sub_categories', 'assets.sub_category_id', '=', 'sub_categories.id')
            ->leftJoin('companies', 'assets.company_id', '=', 'companies.id')
            ->select(
                'categories.name as category_name',
                DB::raw('COALESCE(sub_categories.name, assets.nama_barang) as sub_category_display_name'),
                'assets.kondisi',
                'companies.name as company_name', // Nama perusahaan dibutuhkan untuk grouping & display
                 // Ambil ID Aset yang relevan dalam grup ini
                DB::raw('GROUP_CONCAT(assets.id) as asset_ids'),
                DB::raw('count(assets.id) as total_assets'),
                DB::raw('sum(assets.harga_total) as total_value')
            )
            ->groupBy('categories.name', 'sub_category_display_name', 'assets.kondisi', 'companies.name')
            ->orderBy('categories.name')
            ->orderBy('sub_category_display_name')
            ->orderBy('companies.name');

        // --- FILTER HAK AKSES PERUSAHAAN ---
        $user = Auth::user();
        $allowedCompanyIds = collect(); // Default collection kosong
        if (!$user->hasRole('super-admin')) {
            try {
                $allowedCompanyIds = $user->companies()->pluck('companies.id');
                $query->whereIn('assets.company_id', $allowedCompanyIds);
            } catch (\Exception $e) {
                Log::error("Error getting allowed companies for user {$user->id}: " . $e->getMessage());
                $query->whereRaw('1 = 0');
            }
        }
        // --- AKHIR FILTER PERUSAHAAN ---

        // Filter dari request form
        if ($request->filled('category_id')) {
            $query->where('assets.category_id', $request->category_id);
        }
        if ($request->filled('sub_category_id')) {
            $query->where('assets.sub_category_id', $request->sub_category_id);
        }
        if ($request->filled('company_id')) {
             if ($user->hasRole('super-admin') || $allowedCompanyIds->contains($request->company_id)) {
                 $query->where('assets.company_id', $request->company_id);
             } else {
                 $query->whereRaw('1 = 0');
             }
        }
        if ($request->filled('kondisi')) {
            $query->where('assets.kondisi', $request->kondisi);
        }
        if ($request->filled('asset_user_id')) {
             // Filter berdasarkan pengguna hanya relevan jika data pengguna ada di join/select
             // Untuk summary, ini mungkin tidak perlu, tapi jika diperlukan, tambahkan join ke asset_users
             // $query->where('assets.asset_user_id', $request->asset_user_id);
        }
         // Filter Spesifikasi (perlu join assets jika belum)
        if ($request->filled('spec_key') && $request->filled('spec_value')) {
            $key = $request->input('spec_key');
            $value = $request->input('spec_value');
            $jsonColumn = 'assets.specifications';
            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT({$jsonColumn}, '$.\"{$key}\"')) = ?", [$value]);
        }

        return $query;
    }


    /**
     * Helper Function untuk mendapatkan query Laporan Tracking
     */
    protected function getTrackingQuery(Request $request): Builder
    {
        $query = Asset::query()
            ->with(['category', 'subCategory', 'company', 'assetUser.company'])
            ->whereNotNull('asset_user_id');

        // --- FILTER HAK AKSES PERUSAHAAN ---
        $user = Auth::user();
        $allowedCompanyIds = collect(); // Default
        if (!$user->hasRole('super-admin')) {
             try {
                $allowedCompanyIds = $user->companies()->pluck('companies.id');
                $query->whereIn('company_id', $allowedCompanyIds);
             } catch (\Exception $e) {
                 Log::error("Error getting allowed companies for user {$user->id}: " . $e->getMessage());
                 $query->whereRaw('1 = 0');
             }
        }
        // --- AKHIR FILTER PERUSAHAAN ---

        // Terapkan Filter dari request form
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->sub_category_id);
        }
         if ($request->filled('company_id')) {
             if ($user->hasRole('super-admin') || $allowedCompanyIds->contains($request->company_id)) {
                 $query->where('company_id', $request->company_id);
             } else {
                 $query->whereRaw('1 = 0');
             }
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
                  ->orWhereHas('assetUser', function($uq) use ($search){
                        $uq->where('nama', 'like', '%' . $search . '%');
                  });
            });
        }

        return $query;
    }
    // =========================================================================
    // VIEW REPORT METHODS
    // =========================================================================

    public function inventoryReport(Request $request)
    {
        $user = Auth::user();
        $categories = Category::orderBy('name')->get();
        $subCategories = SubCategory::with('category')->orderBy('name')->get();

        $allowedCompanyIds = collect(); // Inisialisasi
        if (!$user->hasRole('super-admin')) {
             try {
                $allowedCompanyIds = $user->companies()->pluck('companies.id');
                $companies = Company::whereIn('id', $allowedCompanyIds)->orderBy('name')->get();
                $assetUsers = AssetUser::whereIn('company_id', $allowedCompanyIds)->orderBy('nama')->get();
             } catch (\Exception $e) {
                 Log::error("Error getting filter data for user {$user->id}: " . $e->getMessage());
                 $companies = collect(); // Kosongkan jika error
                 $assetUsers = collect();
             }
        } else {
            $companies = Company::orderBy('name')->get();
            $assetUsers = AssetUser::orderBy('nama')->get();
        }

        $selectedFilters = $request->all();

        // --- PENGUMPULAN DATA SPESIFIKASI UNIK ---
        $allUniqueSpecValues = [];
        $assetsQueryForSpecs = Asset::select('sub_category_id', 'specifications');
         if (!$user->hasRole('super-admin')) {
             $assetsQueryForSpecs->whereIn('company_id', $allowedCompanyIds);
         }
        $allAssetsForSpecs = $assetsQueryForSpecs->get();
        // (Logika loop untuk unique specs tetap sama)
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

        // --- DAPATKAN QUERY SUMMARY DARI HELPER ---
        $inventorySummaryQuery = $this->getInventorySummaryQuery($request);
        // --- END DAPATKAN QUERY ---

        // Eksekusi query summary
        $summaryResults = $inventorySummaryQuery->get();

        // Ambil SEMUA ID aset dari hasil summary
        $allAssetIds = $summaryResults->pluck('asset_ids')
                                ->flatMap(function ($ids) {
                                    return explode(',', $ids); // Pisahkan ID yang digabung oleh GROUP_CONCAT
                                })
                                ->unique()
                                ->filter() // Hapus nilai kosong jika ada
                                ->values()
                                ->toArray();

        // Query TERPISAH untuk mengambil detail aset berdasarkan ID yang relevan
        // Eager load relasi yang dibutuhkan untuk detail
        $assetDetails = Asset::with(['assetUser.company', 'company'])
                             ->whereIn('id', $allAssetIds)
                             ->get()
                             ->keyBy('id'); // Jadikan ID sebagai key agar mudah diakses

        // Proses hasil summary dan tambahkan detail aset
         $inventorySummary = $summaryResults->map(function ($item) use ($assetDetails) {
              // Ambil ID aset untuk item summary ini
             $detailAssetIds = explode(',', $item->asset_ids);

             // Ambil objek aset detail dari collection $assetDetails
             $details = collect($detailAssetIds)
                 ->map(function ($id) use ($assetDetails) {
                     return $assetDetails->get((int)$id); // Ambil berdasarkan ID
                 })
                 ->filter(); // Hapus null jika ada ID yang tidak ditemukan

             return [
                 'category_name' => $item->category_name,
                 'sub_category_display_name' => $item->sub_category_display_name,
                 'company_name' => $item->company_name ?? 'N/A',
                 'kondisi' => $item->kondisi,
                 'count' => $item->total_assets, // Gunakan count dari query
                 'total_harga' => $item->total_value, // Gunakan sum dari query
                 'details' => $details, // <-- Simpan collection objek Asset di sini
             ];
         })->sortBy('company_name')->sortBy('sub_category_display_name')->sortBy('category_name');


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
         $user = Auth::user();
         $categories = Category::orderBy('name')->get();
         $subCategories = SubCategory::orderBy('category_id')->orderBy('name')->get();
         $locations = Asset::select('lokasi')->distinct()->whereNotNull('lokasi'); // Query builder awal

         $allowedCompanyIds = collect(); // Inisialisasi
         if (!$user->hasRole('super-admin')) {
             try {
                $allowedCompanyIds = $user->companies()->pluck('companies.id');
                $companies = Company::whereIn('id', $allowedCompanyIds)->orderBy('name')->get();
                $assetUsers = AssetUser::whereIn('company_id', $allowedCompanyIds)->orderBy('nama')->get();
                // Filter lokasi juga berdasarkan hak akses company
                $locations->whereIn('company_id', $allowedCompanyIds);
             } catch (\Exception $e) {
                 Log::error("Error getting filter data for user {$user->id} (tracking): " . $e->getMessage());
                 $companies = collect();
                 $assetUsers = collect();
                 $locations->whereRaw('1 = 0'); // Jangan tampilkan lokasi jika error
             }
         } else {
             $companies = Company::orderBy('name')->get();
             $assetUsers = AssetUser::orderBy('nama')->get();
             // Super admin bisa lihat semua lokasi
         }
         // Selesaikan query lokasi
         $locations = $locations->pluck('lokasi')->sort()->values()->toArray();


        $trackingQuery = $this->getTrackingQuery($request);
        $currentAllocations = $trackingQuery->paginate(20)->withQueryString();

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
    // EXPORT METHODS (Perlu sedikit penyesuaian untuk Inventory Export)
    // =========================================================================

    public function exportInventoryExcel(Request $request)
    {
        // Export Excel biasanya berisi data summary, bukan detail
        $inventorySummary = $this->getInventorySummaryQuery($request)->get(); // Gunakan query summary
        if ($inventorySummary->isEmpty()) {
             return redirect()->route('reports.inventory')->with('error', 'Tidak ada data inventaris yang sesuai untuk diexport.');
        }
        // Pastikan class Export Anda mengharapkan data summary ini
        return Excel::download(new InventorySummaryExport($inventorySummary), 'laporan_inventaris_' . date('Ymd_His') . '.xlsx');
    }

    public function exportInventoryPDF(Request $request)
    {
        // Export PDF juga biasanya berisi data summary
        $inventorySummary = $this->getInventorySummaryQuery($request)->get(); // Gunakan query summary
        if ($inventorySummary->isEmpty()) {
             return redirect()->route('reports.inventory')->with('error', 'Tidak ada data inventaris yang sesuai untuk dicetak.');
        }
        // Pastikan view PDF Anda (pdf-inventory.blade.php) bisa menampilkan data summary ini
        $pdf = Pdf::loadView('reports.pdf-inventory', [
            'inventorySummary' => $inventorySummary,
            'filters' => $request->all(),
        ]);
        return $pdf->download('laporan_inventaris_' . date('Ymd_His') . '.pdf');
    }

    public function exportTrackingExcel(Request $request)
    {
        $currentAllocations = $this->getTrackingQuery($request)->get();
         if ($currentAllocations->isEmpty()) {
             return redirect()->route('reports.tracking')->with('error', 'Tidak ada data alokasi yang sesuai untuk diexport.');
        }
        return Excel::download(new TrackingReportExport($currentAllocations), 'laporan_alokasi_' . date('Ymd_His') . '.xlsx');
    }

    public function exportTrackingPDF(Request $request)
    {
        $currentAllocations = $this->getTrackingQuery($request)->get();
         if ($currentAllocations->isEmpty()) {
             return redirect()->route('reports.tracking')->with('error', 'Tidak ada data alokasi yang sesuai untuk dicetak.');
        }
        $pdf = Pdf::loadView('reports.pdf-tracking', [
            'currentAllocations' => $currentAllocations,
            'filters' => $request->all(),
        ]);
        return $pdf->download('laporan_alokasi_' . date('Ymd_His') . '.pdf');
    }
}

