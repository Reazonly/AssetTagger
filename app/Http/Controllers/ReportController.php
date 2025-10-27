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
use Illuminate\Support\Facades\Log;   // <-- Tambahkan Log

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
        // Query dasar sudah menggabungkan data yang diperlukan untuk summary
        $query = Asset::query()
            ->join('categories', 'assets.category_id', '=', 'categories.id')
            ->leftJoin('sub_categories', 'assets.sub_category_id', '=', 'sub_categories.id')
             // Tambahkan join ke companies untuk mendapatkan nama perusahaan
            ->leftJoin('companies', 'assets.company_id', '=', 'companies.id')
            ->select(
                'categories.name as category_name',
                DB::raw('COALESCE(sub_categories.name, assets.nama_barang) as sub_category_display_name'),
                'assets.kondisi',
                // Pilih juga company name
                'companies.name as company_name',
                DB::raw('count(assets.id) as total_assets'),
                DB::raw('sum(assets.harga_total) as total_value')
            )
            // Group by company name juga
            ->groupBy('categories.name', 'sub_category_display_name', 'assets.kondisi', 'companies.name')
            ->orderBy('categories.name')
            ->orderBy('sub_category_display_name')
            ->orderBy('companies.name'); // Urutkan juga berdasarkan company

        // --- FILTER HAK AKSES PERUSAHAAN ---
        $user = Auth::user();
        if (!$user->hasRole('super-admin')) {
            try {
                $allowedCompanyIds = $user->companies()->pluck('companies.id');
                // Gunakan 'assets.company_id' karena ada join
                $query->whereIn('assets.company_id', $allowedCompanyIds);
            } catch (\Exception $e) {
                Log::error("Error getting allowed companies for user {$user->id}: " . $e->getMessage());
                // Jika error, mungkin batasi agar tidak melihat apa pun
                $query->whereRaw('1 = 0'); // Kondisi yang selalu false
            }
        }
        // --- AKHIR FILTER PERUSAHAAN ---

        // Filter dari request form (tetap berlaku setelah filter hak akses)
        if ($request->filled('category_id')) {
            $query->where('assets.category_id', $request->category_id);
        }
        if ($request->filled('sub_category_id')) {
            $query->where('assets.sub_category_id', $request->sub_category_id);
        }
        if ($request->filled('company_id')) {
            // Pengguna hanya bisa memfilter lebih lanjut dari company yang diizinkan
             if ($user->hasRole('super-admin') || in_array($request->company_id, $allowedCompanyIds->toArray())) {
                 $query->where('assets.company_id', $request->company_id);
             } else {
                 // Jika user mencoba filter company di luar hak aksesnya, return no result
                 $query->whereRaw('1 = 0');
             }
        }
        if ($request->filled('kondisi')) {
            $query->where('assets.kondisi', $request->kondisi);
        }
        if ($request->filled('asset_user_id')) {
             $query->where('assets.asset_user_id', $request->asset_user_id);
        }
         // Filter Spesifikasi
        if ($request->filled('spec_key') && $request->filled('spec_value')) {
            $key = $request->input('spec_key');
            $value = $request->input('spec_value');
            $jsonColumn = 'assets.specifications'; // Gunakan alias tabel
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
            ->with(['category', 'subCategory', 'company', 'assetUser.company']) // Eager load company dari assetUser
            ->whereNotNull('asset_user_id');

        // --- FILTER HAK AKSES PERUSAHAAN ---
        $user = Auth::user();
        if (!$user->hasRole('super-admin')) {
             try {
                $allowedCompanyIds = $user->companies()->pluck('companies.id');
                // Filter berdasarkan company_id milik ASET itu sendiri
                $query->whereIn('company_id', $allowedCompanyIds);
             } catch (\Exception $e) {
                 Log::error("Error getting allowed companies for user {$user->id}: " . $e->getMessage());
                 $query->whereRaw('1 = 0');
             }
        }
        // --- AKHIR FILTER PERUSAHAAN ---

        // Terapkan Filter dari request form (setelah filter hak akses)
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->sub_category_id);
        }
         if ($request->filled('company_id')) {
             // Pengguna hanya bisa memfilter lebih lanjut dari company yang diizinkan
             if ($user->hasRole('super-admin') || in_array($request->company_id, $allowedCompanyIds->toArray())) {
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
        // Ambil data untuk dropdown filter (bisa difilter juga jika perlu)
        $user = Auth::user();
        $categories = Category::orderBy('name')->get();
        $subCategories = SubCategory::with('category')->orderBy('name')->get();

        // Ambil companies berdasarkan hak akses user
        if (!$user->hasRole('super-admin')) {
            $allowedCompanyIds = $user->companies()->pluck('companies.id');
            $companies = Company::whereIn('id', $allowedCompanyIds)->orderBy('name')->get();
            // Mungkin filter AssetUser juga? Tergantung kebutuhan
             $assetUsers = AssetUser::whereIn('company_id', $allowedCompanyIds)->orderBy('nama')->get();
        } else {
            $companies = Company::orderBy('name')->get();
            $assetUsers = AssetUser::orderBy('nama')->get();
        }

        $selectedFilters = $request->all();

        // --- PENGUMPULAN DATA SPESIFIKASI UNIK ---
        $allUniqueSpecValues = [];
        // Ambil data spesifikasi hanya dari aset yang boleh diakses user
        $assetsQueryForSpecs = Asset::select('sub_category_id', 'specifications');
         if (!$user->hasRole('super-admin')) {
             $assetsQueryForSpecs->whereIn('company_id', $allowedCompanyIds);
         }
        $allAssetsForSpecs = $assetsQueryForSpecs->get();

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
        // Grouping dan Mapping sekarang dilakukan di query helper, jadi kita ambil langsung
        $inventorySummary = $inventoryQuery->get();

        // Ubah struktur data agar sesuai dengan view (jika view mengharapkan grouping)
         $inventorySummary = $inventorySummary->groupBy(function($item) {
             // Kunci unik untuk rekapitulasi: Kategori|SubKategori|Perusahaan|Kondisi
             return $item->category_name . '|' . $item->sub_category_display_name . '|' . ($item->company_name ?? 'N/A') . '|' . $item->kondisi;
         })->map(function ($groupedItems, $key) {
             $firstItem = $groupedItems->first();
             // Sum dari hasil query yang sudah diagregasi (seharusnya hanya ada 1 item per grup unik ini)
             $totalHarga = $groupedItems->sum('total_value');
             $totalAssets = $groupedItems->sum('total_assets');

             // Ambil ID detail aset (jika diperlukan untuk fitur expand)
             // Ini membutuhkan query TERPISAH karena query summary sudah diagregasi
             $detailAssetIds = Asset::where('category_id', Category::where('name', $firstItem->category_name)->value('id'))
                 // ->where('sub_category_id', SubCategory::where('name', $firstItem->sub_category_display_name)->value('id')) // Bisa kompleks jika display name = nama barang
                 ->where('kondisi', $firstItem->kondisi)
                 ->where('company_id', Company::where('name', $firstItem->company_name)->value('id'))
                 // Tambahkan filter hak akses perusahaan lagi di sini
                 ->when(!Auth::user()->hasRole('super-admin'), function($q) {
                      $q->whereIn('company_id', Auth::user()->companies()->pluck('companies.id'));
                 })
                 ->pluck('id')->toArray(); // Ambil ID

             return [
                 'category_name' => $firstItem->category_name,
                 'sub_category_display_name' => $firstItem->sub_category_display_name,
                 'company_name' => $firstItem->company_name ?? 'N/A', // Sertakan company name
                 'kondisi' => $firstItem->kondisi,
                 'count' => $totalAssets,
                 'total_harga' => $totalHarga,
                 'details_ids' => $detailAssetIds, // Sertakan ID detail
             ];
         })->sortBy('company_name')->sortBy('sub_category_display_name')->sortBy('category_name'); // Urutkan


        return view('reports.inventory', compact(
            'inventorySummary',
            'categories',
            'subCategories',
            'companies', // Kirim companies yang sudah difilter
            'assetUsers', // Kirim assetUsers yang sudah difilter
            'allUniqueSpecValues',
            'selectedFilters'
        ));
    }


    public function trackingReport(Request $request)
    {
        // Ambil data master untuk filter (dengan filter hak akses)
         $user = Auth::user();
         $categories = Category::orderBy('name')->get();
         $subCategories = SubCategory::orderBy('category_id')->orderBy('name')->get();
         $locations = Asset::select('lokasi')->distinct()->whereNotNull('lokasi')->pluck('lokasi')->sort()->values()->toArray();

         // Ambil companies berdasarkan hak akses user
         if (!$user->hasRole('super-admin')) {
             $allowedCompanyIds = $user->companies()->pluck('companies.id');
             $companies = Company::whereIn('id', $allowedCompanyIds)->orderBy('name')->get();
             // Mungkin filter AssetUser juga?
             $assetUsers = AssetUser::whereIn('company_id', $allowedCompanyIds)->orderBy('nama')->get();
         } else {
             $companies = Company::orderBy('name')->get();
             $assetUsers = AssetUser::orderBy('nama')->get();
         }


        // --- DAPATKAN QUERY DARI HELPER (Sudah termasuk filter hak akses & request) ---
        $trackingQuery = $this->getTrackingQuery($request);
        // --- END DAPATKAN QUERY ---

        // Eksekusi query dengan paginasi
        $currentAllocations = $trackingQuery->paginate(20)->withQueryString(); // Tambahkan withQueryString

        return view('reports.tracking', [
            'assets' => $currentAllocations,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'assetUsers' => $assetUsers, // Kirim assetUsers yang sudah difilter
            'companies' => $companies,   // Kirim companies yang sudah difilter
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

