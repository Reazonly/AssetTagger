<?php 
namespace App\Http\Controllers; 

use App\Models\Asset;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\AssetUser;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf; // Pastikan menggunakan Facade yang benar
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Exports\InventorySummaryExport;
use App\Exports\TrackingReportExport;

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
                DB::raw('GROUP_CONCAT(assets.id) as asset_ids'), // Ambil ID Aset yang relevan
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
                // Ambil ID company yang boleh diakses
                $allowedCompanyIds = $user->companies()->pluck('companies.id');
                if ($allowedCompanyIds->isEmpty()) {
                    // Jika user tidak punya akses ke company manapun, jangan tampilkan apa-apa
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('assets.company_id', $allowedCompanyIds);
                }
            } catch (\Exception $e) {
                Log::error("Error getting allowed companies for user {$user->id}: " . $e->getMessage());
                $query->whereRaw('1 = 0'); // Kondisi yang selalu false jika error
            }
        }
        // --- AKHIR FILTER PERUSAHAAN ---

        // Filter dari request form (Jalankan SETELAH filter hak akses)
        if ($request->filled('category_id')) {
            $query->where('assets.category_id', $request->category_id);
        }
        if ($request->filled('sub_category_id')) {
            $query->where('assets.sub_category_id', $request->sub_category_id);
        }
        if ($request->filled('company_id')) {
             // Pastikan user hanya bisa filter company yang memang boleh diakses
             if ($user->hasRole('super-admin') || $allowedCompanyIds->contains($request->company_id)) {
                 $query->where('assets.company_id', $request->company_id);
             } else {
                 // Jika mencoba filter company di luar hak akses, return no result
                 $query->whereRaw('1 = 0');
             }
        }
        if ($request->filled('kondisi')) {
            $query->where('assets.kondisi', $request->kondisi);
        }
        // Filter Spesifikasi
        if ($request->filled('spec_key') && $request->filled('spec_value')) {
            $key = $request->input('spec_key');
            $value = $request->input('spec_value');
            $jsonColumn = 'assets.specifications';
            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT({$jsonColumn}, '$.\"{$key}\"')) = ?", [$value]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function (Builder $q) use ($search) {
                $q->where('assets.code_asset', 'like', '%' . $search . '%')
                  ->orWhere('assets.nama_barang', 'like', '%' . $search . '%')
                  ->orWhere('assets.serial_number', 'like', '%' . $search . '%')
                  // Tambahkan pencarian di kolom Specifications (JSON)
                  // Ini penting agar pencarian "ROG" di kolom spesifikasi bisa ditemukan.
                  ->orWhere('assets.specifications', 'like', '%' . $search . '%') 
                  // Cari berdasarkan kategori dan sub-kategori
                  ->orWhere('categories.name', 'like', '%' . $search . '%')
                  ->orWhere('sub_categories.name', 'like', '%' . $search . '%');
            });
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
                if ($allowedCompanyIds->isEmpty()) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('company_id', $allowedCompanyIds);
                }
             } catch (\Exception $e) {
                 Log::error("Error getting allowed companies for user {$user->id} (tracking): " . $e->getMessage());
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
                  // Tambahkan pencarian di kolom Specifications (JSON)
                  ->orWhere('specifications', 'like', '%' . $search . '%') 
                  // Cari berdasarkan Nama Pengguna
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
                // Jika user tidak punya akses company, $companies akan kosong
                $companies = Company::whereIn('id', $allowedCompanyIds)->orderBy('name')->get();
                // Filter assetUsers berdasarkan company yg boleh diakses
                $assetUsers = AssetUser::whereIn('company_id', $allowedCompanyIds)->orderBy('nama')->get();
             } catch (\Exception $e) {
                 Log::error("Error getting filter data for user {$user->id} (inventory): " . $e->getMessage());
                 $companies = collect();
                 $assetUsers = collect();
                 $allowedCompanyIds = collect([-1]); // Set ID yg tidak mungkin ada jika error
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
             // Pastikan $allowedCompanyIds tidak kosong sebelum whereIn
             if($allowedCompanyIds->isNotEmpty()){
                 $assetsQueryForSpecs->whereIn('company_id', $allowedCompanyIds);
             } else {
                 // Jika tidak punya akses company, jangan ambil spec apa pun
                 $assetsQueryForSpecs->whereRaw('1 = 0');
             }
         }
        $allAssetsForSpecs = $assetsQueryForSpecs->get();

        // (Logika loop untuk unique specs tetap sama)
         foreach ($allAssetsForSpecs as $asset) {
             $specs = is_string($asset->specifications) ? json_decode($asset->specifications, true) : $asset->specifications;
             if (!is_array($specs) || !$asset->sub_category_id) continue;
             if (!isset($allUniqueSpecValues[$asset->sub_category_id])) $allUniqueSpecValues[$asset->sub_category_id] = [];
             foreach ($specs as $key => $value) {
                 $formKey = $key;
                 if (!isset($allUniqueSpecValues[$asset->sub_category_id][$formKey])) $allUniqueSpecValues[$asset->sub_category_id][$formKey] = collect();
                 if (!is_null($value) && $value !== '') $allUniqueSpecValues[$asset->sub_category_id][$formKey]->push($value);
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

         // Ambil SEMUA ID aset dari hasil summary (jika ada hasil)
        $allAssetIds = [];
        if ($summaryResults->isNotEmpty()) {
            $allAssetIds = $summaryResults->pluck('asset_ids')
                                ->flatMap(function ($ids) {
                                     // Pastikan $ids tidak null atau kosong sebelum explode
                                     return $ids ? explode(',', $ids) : [];
                                })
                                ->map(fn($id) => (int)$id) // Konversi ke integer
                                ->unique()
                                ->filter() // Hapus nilai 0 atau false
                                ->values()
                                ->toArray();
        }


        // Query TERPISAH untuk mengambil detail aset (hanya jika ada ID yang ditemukan)
        $assetDetails = collect(); // Default collection kosong
        if (!empty($allAssetIds)) {
            $assetDetails = Asset::with(['assetUser.company', 'company'])
                                 ->whereIn('id', $allAssetIds)
                                 ->get()
                                 ->keyBy('id');
        }


        // Proses hasil summary dan tambahkan detail aset
         $inventorySummary = $summaryResults->map(function ($item) use ($assetDetails) {
              // Ambil ID aset untuk item summary ini
              $detailAssetIds = $item->asset_ids ? explode(',', $item->asset_ids) : [];

              // Ambil objek aset detail dari collection $assetDetails
              $details = collect($detailAssetIds)
                  ->map(function ($id) use ($assetDetails) {
                       // Pastikan ID valid sebelum mengambil dari collection
                       return $id ? $assetDetails->get((int)$id) : null;
                   })
                   ->filter(); // Hapus null jika ada ID yang tidak valid atau tidak ditemukan

               return [
                   'category_name' => $item->category_name,
                   'sub_category_display_name' => $item->sub_category_display_name,
                   'company_name' => $item->company_name ?? 'N/A',
                   'kondisi' => $item->kondisi,
                   'count' => (int)$item->total_assets, // Cast ke integer
                   'total_harga' => (float)$item->total_value, // Cast ke float
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
         $locations = Asset::select('lokasi')->distinct()->whereNotNull('lokasi');

         $allowedCompanyIds = collect(); // Inisialisasi
         if (!$user->hasRole('super-admin')) {
             try {
                 $allowedCompanyIds = $user->companies()->pluck('companies.id');
                 if($allowedCompanyIds->isEmpty()){
                      // Jika tidak punya akses company, batasi filter & query lokasi
                      $companies = collect();
                      $assetUsers = collect();
                      $locations->whereRaw('1 = 0');
                 } else {
                      $companies = Company::whereIn('id', $allowedCompanyIds)->orderBy('name')->get();
                      $assetUsers = AssetUser::whereIn('company_id', $allowedCompanyIds)->orderBy('nama')->get();
                      $locations->whereIn('company_id', $allowedCompanyIds);
                 }
             } catch (\Exception $e) {
                  Log::error("Error getting filter data for user {$user->id} (tracking): " . $e->getMessage());
                  $companies = collect();
                  $assetUsers = collect();
                  $locations->whereRaw('1 = 0');
             }
         } else {
             $companies = Company::orderBy('name')->get();
             $assetUsers = AssetUser::orderBy('nama')->get();
             // Super admin bisa lihat semua lokasi
         }
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
    // EXPORT METHODS 
    // =========================================================================

    public function exportInventoryExcel(Request $request)
    {
        // 1. Ambil ID Aset dari Query String
        $ids = explode(',', $request->input('ids'));

        if (empty(array_filter($ids))) {
            return redirect()->route('reports.inventory')->with('error', 'Silakan pilih setidaknya satu aset untuk diexport.');
        }

        // 2. Ambil DATA DETAIL ASET secara lengkap (dengan Eager Loading)
        $assetsCollection = Asset::whereIn('id', $ids)
            // Eager Loading Wajib untuk mengisi kolom Kategori, Perusahaan, dan Pengguna
            ->with(['subCategory.category', 'company', 'assetUser']) 
            ->get(); // Ambil sebagai Collection (sesuai konstruktor Export Class)
        
        if ($assetsCollection->isEmpty()) {
             return redirect()->route('reports.inventory')->with('error', 'Tidak ada data aset yang ditemukan.');
        }

        // 3. Kirim Collection Data Detail ke Export Class
        return Excel::download(new InventorySummaryExport($assetsCollection), 'laporan_detail_aset_' . date('Ymd_His') . '.xlsx');
    }

    public function exportInventoryPDF(Request $request)
    {
        // --- 1. LOGIKA PENGAMBILAN ASET BERDASARKAN PEMILIHAN ATAU FILTER ---
        if ($request->has('asset_ids')) {
            // Logika 1: CETAK TERPILIH (Hanya aset yang dicentang)
            $ids = explode(',', $request->input('asset_ids'));
            $ids = array_filter($ids); // Bersihkan ID kosong

            if (empty($ids)) {
                 return redirect()->route('reports.inventory')->with('error', 'Tidak ada aset yang dipilih untuk dicetak.');
            }
            
            $assets = Asset::whereIn('id', $ids)
                            ->with(['subCategory.category', 'assetUser.company', 'company'])
                            ->get();
        } else {
            // Logika 2: CETAK FILTERED (Fallback: Ambil ID dari hasil filter halaman web)
            
            // Dapatkan hasil summary query (yang sudah terfilter)
            $inventorySummaryQuery = $this->getInventorySummaryQuery($request);
            $summaryResults = $inventorySummaryQuery->get();
            
            // Ekstrak semua ID aset dari hasil summary
            $allAssetIds = $summaryResults->pluck('asset_ids')
                                        ->flatMap(fn($ids) => $ids ? explode(',', $ids) : [])
                                        ->unique()
                                        ->filter()
                                        ->values()
                                        ->toArray();
                                        
            // Ambil DATA DETAIL ASET secara lengkap berdasarkan ID yang sudah difilter
            if (empty($allAssetIds)) {
                $assets = collect();
            } else {
                $assets = Asset::whereIn('id', $allAssetIds)
                                ->with(['subCategory.category', 'assetUser.company', 'company'])
                                ->get();
            }
        }
        
        // Cek jika collection aset kosong
        if ($assets->isEmpty()) {
            return redirect()->route('reports.inventory')->with('error', 'Tidak ada data aset yang sesuai untuk dicetak.');
        }

        // --- 2. LOGIKA PENGHITUNGAN UNTUK PDF ---

        $totalHarga = $assets->sum('harga_total');
        $assetCount = $assets->count();

        // Panggil helper function untuk mendapatkan spec keys yang unik
        $specKeys = $this->getUniqueSpecKeys($assets); 
        
        // --- 3. EKSEKUSI DAN RETURN PDF ---
        $pdf = Pdf::loadView('reports.pdf-inventory', compact('assets', 'specKeys', 'totalHarga', 'assetCount'));

        return $pdf->stream('laporan_detail_aset_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Helper Function untuk mendapatkan kunci spesifikasi unik dari koleksi aset.
     */
    private function getUniqueSpecKeys(Collection $assets): array
    {
        $keys = [];
        foreach ($assets as $asset) {
            // Cek apakah specifications adalah string JSON, jika ya, decode.
            $specifications = is_string($asset->specifications) ? json_decode($asset->specifications, true) : $asset->specifications;
            
            if (is_array($specifications)) {
                $keys = array_merge($keys, array_keys($specifications));
            }
        }
        $unwantedKeys = ['perusahaan_pemilik', 'perusahaan_pengguna'];
        $uniqueKeys = array_unique($keys);
        return array_values(array_diff($uniqueKeys, $unwantedKeys));
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