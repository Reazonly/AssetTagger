<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;
use App\Models\Company;
use App\Models\Category;
use App\Models\SubCategory;
use App\Imports\AssetsImport;
use App\Exports\AssetsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AssetController extends Controller
{
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
        if (in_array($category->code, ['ELEC', 'VEHI'])) {
            $jenisBarangCode = $getFourDigits(optional($subCategory)->name);
            $merkCode = $getFourDigits($request->merk);
            return "{$jenisBarangCode}/{$merkCode}/{$companyCode}/{$paddedId}";
        } elseif ($category->code === 'FURN') {
            $namaBarangCode = $getFourDigits($request->nama_barang);
            $kategoriCode = $getFourDigits($category->name);
            return "{$namaBarangCode}/{$kategoriCode}/{$companyCode}/{$paddedId}";
        }
        $kategoriCode = $getFourDigits($category->name);
        $namaBarangCode = $getFourDigits($request->nama_barang);
        return "{$namaBarangCode}/{$kategoriCode}/{$companyCode}/{$paddedId}";
    }
    
    private function getUserIdFromRequest(Request $request): ?int
    {
        if ($request->filled('new_user_name')) {
            $user = User::firstOrCreate(
                ['nama_pengguna' => trim($request->new_user_name)],
                ['jabatan' => $request->jabatan, 'departemen' => $request->departemen]
            );
            return $user->id;
        }
        return $request->user_id;
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
        $query = Asset::with(['user', 'category', 'company', 'subCategory']);
        
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('code_asset', 'like', "%{$searchTerm}%")
                         ->orWhere('nama_barang', 'like', "%{$searchTerm}%")
                         ->orWhere('serial_number', 'like', "%{$searchTerm}%")
                         ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                             $userQuery->where('nama_pengguna', 'like', "%{$searchTerm}%");
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
        return view('assets.create', [
            'users' => User::all(),
            'categories' => Category::with(['units', 'subCategories'])->get(),
            'companies' => Company::all(),
        ]);
    }
    
    public function store(Request $request)
    {
        $category = Category::find($request->category_id);
        $merkRule = $category && $category->requires_merk ? 'required|string|max:255' : 'nullable';
        $tipeRule = $category && !$category->requires_merk && $category->code !== 'FURN' ? 'required|string|max:255' : 'nullable';
        $subCategoryRequired = $category && in_array($category->code, ['ELEC', 'VEHI']);
        $validatedData = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'company_id' => 'required|exists:companies,id',
            'sub_category_id' => $subCategoryRequired ? 'required|exists:sub_categories,id' : 'nullable',
            'merk' => $merkRule,
            'tipe' => $tipeRule,
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
            'serial_number' => 'nullable|string|max:255|unique:assets,serial_number',
            'kondisi' => 'required|string',
            'lokasi' => 'nullable|string|max:255',
            'tanggal_pembelian' => 'nullable|date',
            'harga_total' => 'nullable|numeric|min:0',
            'po_number' => 'nullable|string|max:255',
            'nomor' => 'nullable|string|max:255', // BAST
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
        $data['user_id'] = $this->getUserIdFromRequest($request);
        unset($data['spec']);
        $data['code_asset'] = 'PENDING-' . time();
        $asset = Asset::create($data);
        $subCategory = $request->sub_category_id ? SubCategory::find($request->sub_category_id) : null;
        $asset->code_asset = $this->generateAssetCode($request, $category, $subCategory, $asset->id);
        $asset->save();
        if ($data['user_id']) {
            $asset->history()->create(['user_id' => $data['user_id'], 'tanggal_mulai' => now()]);
        }
        return redirect()->route('assets.show', $asset)->with('success', 'Aset baru berhasil ditambahkan: ' . $asset->code_asset);
    }

    public function show(Asset $asset)
    {
        $asset->load(['user', 'category', 'company', 'subCategory', 'history.user']);
        return view('assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $asset->load(['user', 'category', 'company', 'subCategory']);
        return view('assets.edit', [
            'asset' => $asset,
            'users' => User::all(),
            'categories' => Category::with(['units', 'subCategories'])->get(),
            'companies' => Company::all(),
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        $category = $asset->category;
        $merkRule = $category && $category->requires_merk ? 'required|string|max:255' : 'nullable';
        $tipeRule = $category && !$category->requires_merk && $category->code !== 'FURN' ? 'required|string|max:255' : 'nullable';
        $subCategoryRequired = $category && in_array($category->code, ['ELEC', 'VEHI']);
        $request->validate([
            'sub_category_id' => $subCategoryRequired ? 'required|exists:sub_categories,id' : 'nullable',
            'merk' => $merkRule,
            'tipe' => $tipeRule,
            'serial_number' => 'nullable|string|max:255|unique:assets,serial_number,' . $asset->id,
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
            'kondisi' => 'required|string',
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
        $updateData = $request->except(['_token', '_method', 'nama_barang', 'category_id', 'company_id']);
        $updateData['specifications'] = $this->collectSpecificationsFromRequest($request);
        if ($request->filled('tanggal_pembelian')) {
            $updateData['thn_pembelian'] = Carbon::parse($request->tanggal_pembelian)->format('Y');
        } else {
            $updateData['tanggal_pembelian'] = null;
            $updateData['thn_pembelian'] = null;
        }
        $oldUserId = $asset->user_id;
        $newUserId = $this->getUserIdFromRequest($request);
        $updateData['user_id'] = $newUserId;
        $asset->update($updateData);
        if ($oldUserId != $newUserId) {
            if ($oldUserId) {
                $asset->history()->where('user_id', $oldUserId)->whereNull('tanggal_selesai')->update(['tanggal_selesai' => now()]);
            }
            if ($newUserId) {
                $asset->history()->create(['user_id' => $newUserId, 'tanggal_mulai' => now()]);
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
        $asset->load(['user', 'category', 'company', 'subCategory', 'history.user']);
        return view('assets.public-show', compact('asset'));
    }

    public function getUnits(Category $category)
    {
        return response()->json($category->units);
    }
    
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);
        Excel::import(new AssetsImport, $request->file('file'));
        return redirect()->route('assets.index')->with('success', 'Data aset berhasil diimpor.');
    }

    public function print(Request $request)
    {
        $assetIds = $request->query('ids');
        if ($assetIds && is_array($assetIds) && count($assetIds) > 0) {
            $assets = Asset::with('user')->whereIn('id', $assetIds)->get();
            return view('assets.print', compact('assets'));
        }
        return redirect()->route('assets.index')->with('error', 'Tidak ada aset yang dipilih untuk dicetak.');
    }
    
    public function export(Request $request)
    {
        $assetIds = $request->query('ids');
        if (empty($assetIds)) {
            return redirect()->route('assets.index')->with('error', 'Tidak ada aset yang dipilih untuk diekspor.');
        }
        $firstAsset = Asset::find($assetIds[0]);
        $categoryCode = optional($firstAsset->category)->code;
        $fileName = 'assets_export_' . ($categoryCode ? strtolower($categoryCode) . '_' : '') . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new AssetsExport(null, $assetIds, $categoryCode), $fileName);
    }

    public function downloadPDF(Asset $asset)
    {
        $asset->load(['user', 'category', 'company', 'subCategory', 'history.user']);
        $pdf = Pdf::loadView('assets.pdf', ['asset' => $asset]);

        // --- PERBAIKAN ---
        // Ambil kode aset asli
        $assetCode = $asset->code_asset;
        // Ganti karakter '/' dengan '-' agar menjadi nama file yang valid
        $safeFileName = str_replace('/', '-', $assetCode);

        // Gunakan nama file yang sudah aman
        return $pdf->download('asset-detail-' . $safeFileName . '.pdf');
    }
}