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
            ->select(
                'categories.name as category_name',
                'assets.kondisi',
                DB::raw('count(assets.id) as total_assets'),
                DB::raw('sum(assets.harga_total) as total_value')
            )
            ->groupBy('categories.name', 'assets.kondisi')
            ->orderBy('categories.name');

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
    private function getTrackingQuery(Request $request)
    {
        $query = Asset::query()
            ->with(['assetUser.company', 'category', 'subCategory', 'company'])
            ->whereNotNull('asset_user_id'); // Hanya tampilkan aset yang memiliki pengguna saat ini

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->sub_category_id);
        }
        if ($request->filled('asset_user_id')) {
             $query->where('asset_user_id', $request->asset_user_id);
        }
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id); 
        }
        
        return $query->orderBy('code_asset');
    }
    
    // =========================================================================
    // VIEW REPORT METHODS
    // =========================================================================

    public function inventoryReport(Request $request)
    {
        $categories = Category::orderBy('name')->get();
        $subCategories = SubCategory::orderBy('category_id')->orderBy('name')->get(); 
        $companies = Company::orderBy('name')->get();
        $assetUsers = AssetUser::orderBy('nama')->get(); 
        
        $inventorySummary = $this->getInventoryQuery($request)->get();

        return view('reports.inventory', [
            'inventorySummary' => $inventorySummary,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'companies' => $companies,
            'assetUsers' => $assetUsers,
            'selectedFilters' => $request->all(),
        ]);
    }

    public function trackingReport(Request $request)
    {
        $categories = Category::orderBy('name')->get();
        $subCategories = SubCategory::orderBy('category_id')->orderBy('name')->get(); 
        $assetUsers = AssetUser::orderBy('nama')->get();
        $companies = Company::orderBy('name')->get();

        $currentAllocations = $this->getTrackingQuery($request)->get();

        return view('reports.tracking', [ 
            'currentAllocations' => $currentAllocations,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'assetUsers' => $assetUsers,
            'companies' => $companies,
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