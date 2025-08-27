<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\AssetUser;
use App\Imports\AssetsImport;
use App\Exports\AssetsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
   private function getAssetUserIdFromRequest(Request $request): ?int
    {
        if ($request->filled('new_asset_user_name')) {
            $newUser = AssetUser::create([
                'nama' => $request->input('new_asset_user_name'),
            ]);
            return $newUser->id;
        }
        return $request->input('asset_user_id');
    }

   
    
    private function collectSpecificationsFromRequest(Request $request): array
    {
        $specs = [];
        if (!$request->has('spec')) return $specs;
        
        $allSpecInputs = $request->input('spec', []);
        foreach ($allSpecInputs as $field => $value) {
            if (!is_null($value) && $value !== '') {
                $specs[Str::title(str_replace('_', ' ', $field))] = $value;
            }
        }
        return $specs;
    }

    public function index(Request $request)
    {
        $query = Asset::query()->with(['assetUser', 'category', 'company', 'subCategory']);

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $keywords = explode(' ', $searchTerm);

            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    if (empty($keyword)) continue;
                    
                    $q->where(function ($subQuery) use ($keyword) {
                        $subQuery->where('code_asset', 'like', '%' . $keyword . '%')
                                 ->orWhere('nama_barang', 'like', '%' . $keyword . '%')
                                 ->orWhere('serial_number', 'like', '%' . $keyword . '%')
                                 ->orWhereHas('assetUser', function ($userQuery) use ($keyword) {
                                     $userQuery->where('nama', 'like', '%' . $keyword . '%');
                                 })
                                 ->orWhereHas('category', function ($catQuery) use ($keyword) {
                                     $catQuery->where('name', 'like', '%' . $keyword . '%');
                                 })
                                 ->orWhereHas('subCategory', function ($subCatQuery) use ($keyword) {
                                     $subCatQuery->where('name', 'like', '%' . $keyword . '%');
                                 });
                    });
                }
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        
        // atau seperti ini
        $assets = $query->orderBy('updated_at', 'desc')->paginate(15);

        $categories = Category::orderBy('name')->get();
        return view('assets.index', compact('assets', 'categories'));
    }

    // app/Http/Controllers/AssetController.php

public function create()
{
    // PASTIKAN BARIS INI MENGGUNAKAN with('subCategories')
    $categories = Category::with('subCategories')->orderBy('name')->get();

    $assetUsers = AssetUser::with('company')->orderBy('nama')->get();
    return view('assets.create', [
        'categories' => $categories,
        'companies' => Company::orderBy('name')->get(),
        'users' => $assetUsers,
    ]);
}

    public function store(Request $request)
{
    // 1. Logika untuk menentukan aturan validasi dinamis (INI SUDAH BENAR)
    $subCategory = SubCategory::find($request->sub_category_id);
    $inputType = optional($subCategory)->input_type ?? 'none';
    
    $merkRule = 'nullable|string|max:255';
    $tipeRule = 'nullable|string|max:255';

    if ($inputType === 'merk') { $merkRule = 'required|string|max:255'; }
    if ($inputType === 'tipe') { $tipeRule = 'required|string|max:255'; }
    if ($inputType === 'merk_dan_tipe') {
        if ($request->boolean('use_merk')) { $merkRule = 'required|string|max:255'; }
        if ($request->boolean('use_tipe')) { $tipeRule = 'required|string|max:255'; }
    }
    
    // 2. Lakukan validasi SEKALI SAJA menggunakan aturan dinamis (INI SUDAH BENAR)
    $validatedData = $request->validate([
        'nama_barang' => 'required|string|max:255', 
        'category_id' => 'required|exists:categories,id', 
        'company_id' => 'required|exists:companies,id', 
        'sub_category_id' => 'nullable|exists:sub_categories,id', 
        'asset_user_id' => 'required_without:new_asset_user_name|nullable|exists:asset_users,id', 
        'new_asset_user_name' => 'required_without:asset_user_id|nullable|string|max:255', 
        'merk' => $merkRule, // Menggunakan aturan dinamis
        'tipe' => $tipeRule, // Menggunakan aturan dinamis
        'jumlah' => 'required|integer|min:1', 
        'satuan' => 'required|string|max:50', 
        'serial_number' => 'nullable|string|max:255', 
        'nomor' => 'nullable|string|max:255|unique:assets,nomor', 
        'kondisi' => 'required|string|in:Baik,Rusak,Perbaikan', 
        'lokasi' => 'nullable|string|max:255', 
        'tanggal_pembelian' => 'nullable|date', 
        'harga_total' => 'nullable|numeric|min:0', 
        'po_number' => 'nullable|string|max:255', 
        'code_aktiva' => 'nullable|string|max:255', 
        'sumber_dana' => 'nullable|string|max:255', 
        'include_items' => 'nullable|string', 
        'peruntukan' => 'nullable|string', 
        'keterangan' => 'nullable|string', 
        'spec' => 'nullable|array',
    ]);

    // BLOK VALIDASI KEDUA YANG SALAH SUDAH DIHAPUS DARI SINI

    // 3. Lanjutkan proses (INI SEMUA SUDAH BENAR)
    $data = $validatedData;
    $data['specifications'] = $this->collectSpecificationsFromRequest($request);
    $data['asset_user_id'] = $this->getAssetUserIdFromRequest($request);
    
    unset($data['spec'], $data['new_asset_user_name'], $data['new_asset_user_jabatan'], $data['new_asset_user_departemen']);
    
    $company = Company::find($request->company_id);
    $category = Category::find($request->category_id);
    $data['code_asset'] = Asset::generateNextCode(
        $company, $category, $request->nama_barang, $request->merk, $request->tipe, $request->nomor
    );
    
    $asset = Asset::create($data);

    if ($asset->asset_user_id) {
        $currentUser = AssetUser::find($asset->asset_user_id);
        $asset->history()->create([ 'asset_user_id' => $asset->asset_user_id, 'historical_user_name' => $currentUser->nama ]);
    }

    return redirect()->route('assets.show', $asset)->with('success', 'Aset baru berhasil ditambahkan: ' . $asset->code_asset);
}
    public function show(Asset $asset)
    {
        $asset->load(['assetUser.company', 'category', 'company', 'subCategory', 'history.assetUser.company']);
        return view('assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $asset->load(['assetUser', 'category', 'company', 'subCategory']);
        
        $categories = Category::with('subCategories')->orderBy('name')->get();
        
        $assetUsers = AssetUser::with('company')->orderBy('nama')->get();
        return view('assets.edit', [
            'asset' => $asset,
            'categories' => $categories,
            'companies' => Company::orderBy('name')->get(),
            'users' => $assetUsers,
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'asset_user_id' => 'nullable|exists:asset_users,id',
            'new_asset_user_name' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'nomor' => 'nullable|string|max:255|unique:assets,nomor,' . $asset->id,
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
            'kondisi' => 'required|string|in:Baik,Rusak,Perbaikan',
            'lokasi' => 'nullable|string|max:255',
            'tanggal_pembelian' => 'nullable|date',
            'harga_total' => 'nullable|numeric|min:0',
            'po_number' => 'nullable|string|max:255',
            'code_aktiva' => 'nullable|string|max:255',
            'sumber_dana' => 'nullable|string|max:255',
            'include_items' => 'nullable|string',
            'peruntukan' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'spec' => 'nullable|array',
        ]);
        
        $oldAssetUserId = $asset->asset_user_id;

        $updateData = $validatedData;
        $newAssetUserId = $this->getAssetUserIdFromRequest($request);
        $updateData['asset_user_id'] = $newAssetUserId;
        $updateData['specifications'] = $this->collectSpecificationsFromRequest($request);

        if ($request->filled('tanggal_pembelian')) {
            $updateData['thn_pembelian'] = Carbon::parse($request->tanggal_pembelian)->format('Y');
        } else {
            $updateData['tanggal_pembelian'] = null;
            $updateData['thn_pembelian'] = null;
        }

        unset($updateData['spec'], $updateData['new_asset_user_name']);

        $asset->update($updateData);

        if ($oldAssetUserId != $newAssetUserId) {
            if ($oldAssetUserId) {
                $asset->history()
                      ->where('asset_user_id', $oldAssetUserId)
                      ->whereNull('tanggal_selesai')
                      ->update(['tanggal_selesai' => now()]);
            }
            if ($newAssetUserId) {
                $newUser = AssetUser::find($newAssetUserId);
                $asset->history()->create([
                    'asset_user_id' => $newAssetUserId,
                    'historical_user_name' => $newUser->nama,
                ]);
            }
        }
        
        return redirect()->route('assets.show', $asset->id)->with('success', 'Data aset berhasil diperbarui.');
    }

    public function destroy(Asset $asset)
    {
        try {
            $asset->history()->delete();
            $asset->delete();
            return redirect()->route('assets.index')->with('success', 'Aset berhasil dihapus secara permanen.');
        } catch (\Exception $e) {
            Log::error("Gagal menghapus aset #{$asset->id}: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }

    public function publicShow(Asset $asset)
    {
        $asset->load(['assetUser.company', 'category', 'company', 'subCategory', 'history.assetUser.company']);
        return view('assets.public-show', compact('asset'));
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xls,xlsx,csv']);
        Excel::import(new AssetsImport, $request->file('file'));
        return redirect()->route('assets.index')->with('success', 'Data aset berhasil diimpor.');
    }

    public function print(Request $request)
    {
        $assetIds = $request->query('ids');
        if ($assetIds && is_array($assetIds) && count($assetIds) > 0) {
            $assets = Asset::with('assetUser')->whereIn('id', $assetIds)->get();
            return view('assets.print', compact('assets'));
        }
        return redirect()->route('assets.index')->with('error', 'Tidak ada aset yang dipilih untuk dicetak.');
    }

    public function export(Request $request)
    {
        $selectedIds = $request->query('ids'); 
        $categoryId = $request->query('category_id_export');
        $search = $request->query('search');

        $query = Asset::query();

        if (!empty($selectedIds) && is_array($selectedIds)) {
            $query->whereIn('id', $selectedIds);
            $fileName = 'assets_terpilih_' . date('Y-m-d') . '.xlsx';
        
        } elseif (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
            $category = Category::find($categoryId);
            $fileName = 'assets_' . Str::slug($category->name ?? 'filtered') . '_' . date('Y-m-d') . '.xlsx';
        
        } else {
             if ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('code_asset', 'like', "%{$search}%")
                             ->orWhere('nama_barang', 'like', "%{$search}%")
                             ->orWhere('serial_number', 'like', "%{$search}%");
                });
            }
            $fileName = 'assets_semua_' . date('Y-m-d') . '.xlsx';
        }

        if ($query->count() == 0) {
            return redirect()->route('assets.index')->with('error', 'Tidak ada data yang sesuai untuk diexport.');
        }

        return Excel::download(new AssetsExport($query), $fileName);
    }

    public function downloadPDF(Asset $asset)
    {
        $asset->load(['assetUser', 'category', 'company', 'subCategory', 'history.assetUser']);
        $pdf = Pdf::loadView('assets.pdf', ['asset' => $asset]);
        $safeFileName = str_replace('/', '-', $asset->code_asset);
        return $pdf->download('asset-detail-' . $safeFileName . '.pdf');
    }
}