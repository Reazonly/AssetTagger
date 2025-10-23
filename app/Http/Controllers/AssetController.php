<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\AssetUser;
use App\Exports\AssetsExport;
use App\Imports\AssetsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth; // <-- Pastikan ini ada

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
        $user = Auth::user(); // Gunakan Auth facade
        $query = Asset::query()->with(['assetUser', 'category', 'company', 'subCategory']);

        // --- FILTER PERUSAHAAN ---
        if (!$user->hasRole('super-admin')) {
            $allowedCompanyIds = $user->companies()->pluck('companies.id'); // <-- Perbaikan pluck
            $query->whereIn('company_id', $allowedCompanyIds);
        }
        // --- AKHIR FILTER ---

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
        
        $assets = $query->orderBy('code_asset', 'desc')->paginate(15);
        $assets->withQueryString();

        $categories = Category::orderBy('name')->get();
        return view('assets.index', compact('assets', 'categories'));
    }

    public function create()
    {
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
        $validatedData = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'company_id' => 'required|exists:companies,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'asset_user_id' => 'nullable|exists:asset_users,id',
            'new_asset_user_name' => 'nullable|string|max:255',
            'merk' => 'nullable|string|max:255',
            'tipe' => 'nullable|string|max:255',
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $validatedData;
        $data['specifications'] = $this->collectSpecificationsFromRequest($request);

        if ($request->filled('tanggal_pembelian')) {
            $data['thn_pembelian'] = Carbon::parse($request->tanggal_pembelian)->format('Y');
        } else {
             $data['thn_pembelian'] = null; // Tambahkan ini agar thn_pembelian jadi null jika tanggal kosong
        }
        
        $data['asset_user_id'] = $this->getAssetUserIdFromRequest($request);
        
        unset($data['spec'], $data['new_asset_user_name'], $data['image']);
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('asset_images', 'public');
            $data['image_path'] = $path;
        }
        $company = Company::find($request->company_id);
        $category = Category::find($request->category_id);

        $data['code_asset'] = Asset::generateNextCode(
            $company,
            $category,
            $request->nama_barang,
            $request->merk,
            $request->tipe,
            $request->nomor 
        );
        $asset = Asset::create($data);

        if ($asset->asset_user_id) {
            $currentUser = AssetUser::find($asset->asset_user_id);
            // Pastikan $currentUser ditemukan sebelum mengakses propertinya
            if ($currentUser) { 
                $asset->history()->create([
                    'asset_user_id' => $asset->asset_user_id,
                    'historical_user_name' => $currentUser->nama,
                ]);
            } else {
                // Handle case where user might be deleted between request and history creation
                Log::warning("AssetUser ID {$asset->asset_user_id} not found when creating history for Asset ID {$asset->id}");
                // Anda bisa memilih untuk tidak membuat history atau membuat dengan nama default
                 $asset->history()->create([
                     'asset_user_id' => null, // Atau biarkan null jika FK mengizinkan
                     'historical_user_name' => 'Pengguna Tidak Ditemukan', 
                 ]);
            }
        }

        return redirect()->route('assets.show', $asset)->with('success', 'Aset baru berhasil ditambahkan: ' . $asset->code_asset);
    }

    public function show(Asset $asset)
    {
        // --- TAMBAHKAN VALIDASI AKSES PERUSAHAAN ---
        $user = Auth::user();
        if (!$user->hasRole('super-admin')) {
            $allowedCompanyIds = $user->companies()->pluck('companies.id')->toArray();
            if (!in_array($asset->company_id, $allowedCompanyIds)) {
                return redirect()->route('assets.index')->with('error', 'Anda tidak memiliki hak akses untuk melihat aset ini.');
            }
        }
        // --- AKHIR VALIDASI ---
        
        $asset->load(['assetUser.company', 'category', 'company', 'subCategory', 'history.assetUser.company']);
        return view('assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
         // --- TAMBAHKAN VALIDASI AKSES PERUSAHAAN ---
        $user = Auth::user();
        if (!$user->hasRole('super-admin')) {
            $allowedCompanyIds = $user->companies()->pluck('companies.id')->toArray();
            if (!in_array($asset->company_id, $allowedCompanyIds)) {
                return redirect()->route('assets.index')->with('error', 'Anda tidak memiliki hak akses untuk mengedit aset ini.');
            }
        }
        // --- AKHIR VALIDASI ---

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
         // --- TAMBAHKAN VALIDASI AKSES PERUSAHAAN ---
        $user = Auth::user();
        if (!$user->hasRole('super-admin')) {
            $allowedCompanyIds = $user->companies()->pluck('companies.id')->toArray();
            if (!in_array($asset->company_id, $allowedCompanyIds)) {
                return redirect()->route('assets.index')->with('error', 'Anda tidak memiliki hak akses untuk memperbarui aset ini.');
            }
        }
        // --- AKHIR VALIDASI ---
        
        $validatedData = $request->validate([
            'company_id' => 'required|exists:companies,id', // Pastikan company_id tetap divalidasi
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'remove_image' => 'nullable|boolean',
        ]);
        
        $oldAssetUserId = $asset->asset_user_id;
        $updateData = $validatedData;

        // --- VALIDASI COMPANY_ID SETELAH VALIDASI REQUEST ---
        // Jika user bukan super-admin, pastikan company_id yang baru ada dalam daftar yang diizinkan
        if (!$user->hasRole('super-admin')) {
             if (!in_array($request->input('company_id'), $allowedCompanyIds)) {
                 // Redirect dengan error jika mencoba memindahkan ke perusahaan yang tidak diizinkan
                 return back()->withInput()->withErrors(['company_id' => 'Anda tidak diizinkan memilih perusahaan ini.']);
             }
        }
        // --- AKHIR VALIDASI COMPANY_ID ---

        $newAssetUserId = $this->getAssetUserIdFromRequest($request);
        $updateData['asset_user_id'] = $newAssetUserId;
        $updateData['specifications'] = $this->collectSpecificationsFromRequest($request);

        if ($request->hasFile('image')) {
            if ($asset->image_path) {
                Storage::disk('public')->delete($asset->image_path);
            }
            $path = $request->file('image')->store('asset_images', 'public');
            $updateData['image_path'] = $path;
        } elseif ($request->input('remove_image')) {
            if ($asset->image_path) {
                Storage::disk('public')->delete($asset->image_path);
            }
            $updateData['image_path'] = null;
        }

        if ($request->filled('tanggal_pembelian')) {
            $updateData['thn_pembelian'] = Carbon::parse($request->tanggal_pembelian)->format('Y');
        } else {
            $updateData['tanggal_pembelian'] = null;
            $updateData['thn_pembelian'] = null;
        }

        unset($updateData['spec'], $updateData['new_asset_user_name'], $updateData['image'], $updateData['remove_image']);

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
                 // Pastikan $newUser ditemukan
                if ($newUser) {
                    $asset->history()->create([
                        'asset_user_id' => $newAssetUserId,
                        'historical_user_name' => $newUser->nama,
                    ]);
                } else {
                     Log::warning("AssetUser ID {$newAssetUserId} not found when updating history for Asset ID {$asset->id}");
                     // Opsional: Buat history dengan nama default jika user tidak ditemukan
                     $asset->history()->create([
                         'asset_user_id' => null,
                         'historical_user_name' => 'Pengguna Tidak Ditemukan',
                     ]);
                }
            }
        }
        
        return redirect()->route('assets.show', $asset->id)->with('success', 'Data aset berhasil diperbarui.');
    }

    public function destroy(Asset $asset)
    {
         // --- TAMBAHKAN VALIDASI AKSES PERUSAHAAN ---
        $user = Auth::user();
        if (!$user->hasRole('super-admin')) {
            $allowedCompanyIds = $user->companies()->pluck('companies.id')->toArray();
            if (!in_array($asset->company_id, $allowedCompanyIds)) {
                return redirect()->route('assets.index')->with('error', 'Anda tidak memiliki hak akses untuk menghapus aset ini.');
            }
        }
        // --- AKHIR VALIDASI ---

        if ($asset->image_path) {
            Storage::disk('public')->delete($asset->image_path);
        }
        
        try {
            // Hapus history dulu (opsional, tergantung relasi DB)
            // $asset->history()->delete(); // Uncomment jika perlu
            $asset->delete(); // Ini seharusnya juga menghapus history jika onDelete('cascade')
            return redirect()->route('assets.index')->with('success', 'Aset berhasil dihapus secara permanen.');
        } catch (\Exception $e) {
            Log::error("Gagal menghapus aset #{$asset->id}: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus data. Aset mungkin masih memiliki relasi.');
        }
    }

    public function publicShow(Asset $asset)
    {
        // Public show tidak perlu filter akses
        $asset->load(['assetUser.company', 'category', 'company', 'subCategory', 'history.assetUser.company']);
        return view('assets.public-show', compact('asset'));
    }

    public function import(Request $request)
    {
        // Import mungkin perlu filter jika hanya boleh import ke company tertentu?
        // Untuk saat ini, asumsikan hanya admin/super-admin yang bisa import
        $request->validate(['file' => 'required|mimes:xls,xlsx,csv']);
        Excel::import(new AssetsImport, $request->file('file'));
        return redirect()->route('assets.index')->with('success', 'Data aset berhasil diimpor.');
    }

    public function print(Request $request)
    {
        $assetIds = $request->query('ids');
        if ($assetIds && is_array($assetIds) && count($assetIds) > 0) {
            // --- FILTER ASET YANG BOLEH DICETAK ---
            $user = Auth::user();
            $query = Asset::with('assetUser')->whereIn('id', $assetIds);
            
            if (!$user->hasRole('super-admin')) {
                 $allowedCompanyIds = $user->companies()->pluck('companies.id');
                 $query->whereIn('company_id', $allowedCompanyIds);
            }
            $assets = $query->get();
            // --- AKHIR FILTER ---

            if ($assets->isEmpty()) {
                 return redirect()->route('assets.index')->with('error', 'Tidak ada aset yang sesuai dengan hak akses Anda untuk dicetak.');
            }

            return view('assets.print', compact('assets'));
        }
        return redirect()->route('assets.index')->with('error', 'Tidak ada aset yang dipilih untuk dicetak.');
    }

    public function export(Request $request)
    {
        $selectedIds = $request->query('ids'); 
        $categoryId = $request->query('category_id_export');
        $search = $request->query('search');

        // --- PERBAIKAN DEKLARASI $query ---
        $user = Auth::user(); // Dapatkan user dulu
        $query = Asset::query(); // Deklarasi $query sekali saja

        // --- FILTER PERUSAHAAN ---
        if (!$user->hasRole('super-admin')) {
            $allowedCompanyIds = $user->companies()->pluck('companies.id'); // <-- Perbaikan pluck
            $query->whereIn('company_id', $allowedCompanyIds);
        }
        // --- AKHIR FILTER ---

        // Terapkan filter lain setelah filter perusahaan
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
                    // Mungkin tambahkan orWhereHas user jika perlu difilter juga
                });
            }
            $fileName = 'assets_semua_' . date('Y-m-d') . '.xlsx';
        }

        // Hitung hasil SETELAH semua filter diterapkan
        if ($query->count() == 0) {
            return redirect()->route('assets.index')->with('error', 'Tidak ada data yang sesuai untuk diexport.');
        }

        return Excel::download(new AssetsExport($query), $fileName);
    }

    public function downloadPDF(Asset $asset)
    {
         // --- TAMBAHKAN VALIDASI AKSES PERUSAHAAN ---
        $user = Auth::user();
        if (!$user->hasRole('super-admin')) {
            $allowedCompanyIds = $user->companies()->pluck('companies.id')->toArray();
            if (!in_array($asset->company_id, $allowedCompanyIds)) {
                // Mungkin redirect ke index atau tampilkan pesan error
                 return redirect()->route('assets.index')->with('error', 'Anda tidak memiliki hak akses untuk mengunduh PDF aset ini.');
            }
        }
        // --- AKHIR VALIDASI ---

        $asset->load(['assetUser', 'category', 'company', 'subCategory', 'history.assetUser']);
        $pdf = Pdf::loadView('assets.pdf', ['asset' => $asset]);
        $safeFileName = str_replace(['/', '\\'], '-', $asset->code_asset); // Ganti / dan \\
        return $pdf->download('asset-detail-' . $safeFileName . '.pdf');
    }
}
