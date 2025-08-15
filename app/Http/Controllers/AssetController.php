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
use Barryvdh\DomPDF\Facade\Pdf;

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

    private function generateAssetCode(Request $request, Category $category, ?SubCategory $subCategory, int $assetId): string
    {
        $getFourDigits = function ($string) {
            $cleaned = preg_replace('/[^a-zA-Z0-9]/', '', (string) $string);
            return strtoupper(substr($cleaned, 0, 4));
        };
        $getThreeDigits = function ($string) {
            $cleaned = preg_replace('/[^a-zA-Z0-9]/', '', (string) $string);
            return strtoupper(substr($cleaned, 0, 3));
        };
        $company = Company::find($request->company_id);
        $companyCode = $getThreeDigits(optional($company)->code);
        $paddedId = str_pad($assetId, 3, '0', STR_PAD_LEFT);

        // --- PERBAIKAN LOGIKA DI SINI ---
        // Skenario 1: Untuk Elektronik
        if ($category->code === 'ELEC') {
            $jenisBarangCode = $getFourDigits(optional($subCategory)->name);
            $merkCode = $getFourDigits($request->merk);
            return "{$jenisBarangCode}/{$merkCode}/{$companyCode}/{$paddedId}";
        } 
        // Skenario 2: Khusus untuk Kendaraan
        elseif ($category->code === 'VEHI') {
            $jenisBarangCode = $getFourDigits(optional($subCategory)->name);
            $namaBarangCode = $getFourDigits($request->nama_barang); // Menggunakan nama barang (e.g., Avanza)
            return "{$jenisBarangCode}/{$namaBarangCode}/{$companyCode}/{$paddedId}";
        }
        // Skenario 3: Khusus untuk Furniture
        elseif ($category->code === 'FURN') {
            $jenisBarangCode = $getFourDigits(optional($subCategory)->name);
            $kategoriCode = $getFourDigits($category->code);
            return "{$jenisBarangCode}/{$kategoriCode}/{$companyCode}/{$paddedId}";
        }
        
        // Skenario 4: Untuk semua kategori lainnya
        $kategoriCode = $getFourDigits($category->code);
        $namaBarangCode = $getFourDigits($request->nama_barang);
        return "{$namaBarangCode}/{$kategoriCode}/{$companyCode}/{$paddedId}";
    }

    private function collectSpecificationsFromRequest(Request $request): array
    {
        $specs = [];
        if (!$request->has('spec')) return $specs;
        $allSpecInputs = $request->input('spec', []);
        foreach ($allSpecInputs as $field => $value) {
            if (!empty($value)) {
                $specs[$field] = $value;
            }
        }
        return $specs;
    }

    public function index(Request $request)
    {
        $query = Asset::with(['assetUser', 'category', 'company', 'subCategory']);
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('code_asset', 'like', "%{$searchTerm}%")
                         ->orWhere('nama_barang', 'like', "%{$searchTerm}%")
                         ->orWhere('serial_number', 'like', "%{$searchTerm}%")
                         ->orWhereHas('assetUser', function ($userQuery) use ($searchTerm) {
                             $userQuery->where('nama', 'like', "%{$searchTerm}%");
                         });
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        $assets = $query->latest()->paginate(15);
        $categories = Category::orderBy('name')->get();
        return view('assets.index', compact('assets', 'categories'));
    }

    public function create()
    {
        $categories = Category::with(['subCategories' => function ($query) {
            $query->latest();
        }, 'units'])->orderBy('name')->get();

        $assetUsers = AssetUser::with('company')->orderBy('nama')->get();
        return view('assets.create', [
            'categories' => $categories,
            'companies' => Company::orderBy('name')->get(),
            'users' => $assetUsers,
        ]);
    }

    public function store(Request $request)
    {
        $subCategory = SubCategory::find($request->sub_category_id);
        $inputType = optional($subCategory)->input_type ?? 'none';
        
        $merkRule = 'nullable|string|max:255';
        $tipeRule = 'nullable|string|max:255';

        if ($inputType === 'merk') {
            $merkRule = 'required|string|max:255';
        }
        if ($inputType === 'tipe') {
            $tipeRule = 'required|string|max:255';
        }
        if ($inputType === 'merk_dan_tipe') {
            if ($request->has('use_merk')) {
                $merkRule = 'required|string|max:255';
            }
            if ($request->has('use_tipe')) {
                $tipeRule = 'required|string|max:255';
            }
        }
        
        $validatedData = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'company_id' => 'required|exists:companies,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'asset_user_id' => 'nullable|exists:asset_users,id',
            'new_asset_user_name' => 'nullable|string|max:255',
            'merk' => $merkRule,
            'tipe' => $tipeRule,
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
            'serial_number' => 'nullable|string|max:255|unique:assets,serial_number',
            'kondisi' => 'required|string|in:Baik,Rusak,Perbaikan',
            'lokasi' => 'nullable|string|max:255',
            'tanggal_pembelian' => 'nullable|date',
            'harga_total' => 'nullable|numeric|min:0',
            'po_number' => 'nullable|string|max:255',
            'nomor' => 'nullable|string|max:255',
            'code_aktiva' => 'nullable|string|max:255',
            'sumber_dana' => 'nullable|string|max:255',
            'include_items' => 'nullable|string',
            'peruntukan' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'spec' => 'nullable|array',
        ]);

        $data = $validatedData;
        $data['specifications'] = $this->collectSpecificationsFromRequest($request);

        if ($request->filled('tanggal_pembelian')) {
            $data['thn_pembelian'] = Carbon::parse($request->tanggal_pembelian)->format('Y');
        }
        
        $data['asset_user_id'] = $this->getAssetUserIdFromRequest($request);
        
        unset($data['spec'], $data['new_asset_user_name']);
        
        $data['code_asset'] = 'PENDING-' . time();
        $asset = Asset::create($data);
        
        $category = Category::find($request->category_id);
        $asset->code_asset = $this->generateAssetCode($request, $category, $subCategory, $asset->id);
        $asset->save();

        if ($asset->asset_user_id) {
            $asset->history()->create([
                'asset_user_id' => $asset->asset_user_id,
                'tanggal_mulai' => now(),
            ]);
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
        
        $categories = Category::with(['subCategories' => function ($query) {
            $query->latest();
        }, 'units'])->orderBy('name')->get();
        
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
            'serial_number' => 'nullable|string|max:255|unique:assets,serial_number,' . $asset->id,
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
            'kondisi' => 'required|string|in:Baik,Rusak,Perbaikan',
            'lokasi' => 'nullable|string|max:255',
            'tanggal_pembelian' => 'nullable|date',
            'harga_total' => 'nullable|numeric|min:0',
            'po_number' => 'nullable|string|max:255',
            'nomor' => 'nullable|string|max:255',
            'code_aktiva' => 'nullable|string|max:255',
            'sumber_dana' => 'nullable|string|max:255',
            'include_items' => 'nullable|string',
            'peruntukan' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);
        
        $oldAssetUserId = $asset->asset_user_id;

        $updateData = $validatedData;
        $newAssetUserId = $this->getAssetUserIdFromRequest($request);
        $updateData['asset_user_id'] = $newAssetUserId;

        if ($request->filled('tanggal_pembelian')) {
            $updateData['thn_pembelian'] = Carbon::parse($request->tanggal_pembelian)->format('Y');
        } else {
            $updateData['tanggal_pembelian'] = null;
            $updateData['thn_pembelian'] = null;
        }

        unset($updateData['new_asset_user_name']);

        $asset->update($updateData);

        if ($oldAssetUserId != $newAssetUserId) {
            if ($oldAssetUserId) {
                $asset->history()
                      ->where('asset_user_id', $oldAssetUserId)
                      ->whereNull('tanggal_selesai')
                      ->update(['tanggal_selesai' => now()]);
            }
            if ($newAssetUserId) {
                $asset->history()->create([
                    'asset_user_id' => $newAssetUserId,
                    'tanggal_mulai' => now(),
                ]);
            }
        }
        
        return redirect()->route('assets.show', $asset->id)->with('success', 'Data aset berhasil diperbarui.');
    }

    public function destroy(Asset $asset)
    {
        $asset->history()->delete();
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Aset dan semua riwayatnya berhasil dihapus.');
    }

    public function publicShow(Asset $asset)
    {
        $asset->load(['assetUser.company', 'category', 'company', 'subCategory', 'history.assetUser.company']);
        return view('assets.public-show', compact('asset'));
    }

    public function getUnits(Category $category)
    {
        return response()->json($category->units);
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
        $assetIds = $request->query('ids');
        $categoryCode = $request->query('category_code');
        $search = $request->query('search');

        if (empty($assetIds) && empty($categoryCode)) {
            return redirect()->route('assets.index')->with('error', 'Tidak ada data yang dipilih untuk diekspor.');
        }

        $fileNamePrefix = 'assets_export';
        if ($categoryCode) {
            $fileNamePrefix .= '_' . strtolower($categoryCode);
        }

        $fileName = $fileNamePrefix . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new AssetsExport($search, $assetIds, $categoryCode), $fileName);
    }

    public function downloadPDF(Asset $asset)
    {
        $asset->load(['assetUser', 'category', 'company', 'subCategory', 'history.assetUser']);
        $pdf = Pdf::loadView('assets.pdf', ['asset' => $asset]);
        $safeFileName = str_replace('/', '-', $asset->code_asset);
        return $pdf->download('asset-detail-' . $safeFileName . '.pdf');
    }
}
