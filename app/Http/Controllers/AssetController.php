<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;
use App\Models\Company;
use App\Models\Category;
use App\Imports\AssetsImport;
use App\Exports\AssetsExport; // Ditambahkan
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel; // Pastikan ini ada
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class AssetController extends Controller
{
    /**
     * Menghasilkan kode aset yang unik dan terstruktur berdasarkan master data.
     */
    private function generateAssetCode(Request $request, int $assetId): string
    {
        $namaBarang = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $request->nama_barang), 0, 3));
        
        $category = Category::find($request->category_id);
        $company = Company::find($request->company_id);

        // Menentukan apakah menggunakan 'merk' atau 'tipe' berdasarkan data dari kategori
        $merkOrTipe = optional($category)->requires_merk ? $request->merk : $request->tipe;
        $merkOrTipeCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $merkOrTipe), 0, 3));

        $companyCode = optional($company)->code;
        $paddedId = str_pad($assetId, 3, '0', STR_PAD_LEFT);

        return "{$namaBarang}/{$merkOrTipeCode}/{$companyCode}/{$paddedId}";
    }
    
    /**
     * Mengambil ID pengguna yang ada atau membuat pengguna baru.
     */
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

    /**
     * Menampilkan daftar semua aset dengan filter.
     */
    public function index(Request $request)
    {
        // Start the query with eager loading for better performance
        $query = Asset::with(['user', 'category', 'company']);

        // Apply search filter if a search term is provided
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

        // Apply category filter if a category_id is provided
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Fetch the filtered and paginated assets
        $assets = $query->latest()->paginate(15);
        
        // Fetch all categories to populate the filter dropdown in the view
        $categories = Category::orderBy('name')->get();

        // Return the view with the necessary data
        return view('assets.index', [
            'assets' => $assets,
            'categories' => $categories,
        ]);
    }

    /**
     * Menampilkan form untuk membuat aset baru.
     */
    public function create()
    {
        return view('assets.create', [
            'users' => User::all(),
            'categories' => Category::with('units')->get(),
            'companies' => Company::all(),
        ]);
    }

    /**
     * Menyimpan aset baru ke database.
     */
    public function store(Request $request)
    {
        $category = Category::find($request->category_id);
        $merkRule = $category && $category->requires_merk ? 'required|string|max:255' : 'nullable';
        $tipeRule = $category && !$category->requires_merk ? 'required|string|max:255' : 'nullable';

        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'company_id' => 'required|exists:companies,id',
            'merk' => $merkRule,
            'tipe' => $tipeRule,
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
            'serial_number' => 'nullable|string|max:255|unique:assets,serial_number',
        ]);

        $data = $request->except(['_token']);
        
        if ($request->filled('tanggal_pembelian')) {
            $data['thn_pembelian'] = Carbon::parse($request->tanggal_pembelian)->format('Y');
        }

        $userId = $this->getUserIdFromRequest($request);
        $data['user_id'] = $userId;
        
        $data['code_asset'] = 'PENDING';
        $asset = Asset::create($data);

        $asset->code_asset = $this->generateAssetCode($request, $asset->id);
        $asset->save();

        if ($userId) {
            $asset->history()->create(['user_id' => $userId, 'tanggal_mulai' => now()]);
        }

        return redirect()->route('assets.index')->with('success', 'Aset baru berhasil ditambahkan: ' . $asset->code_asset);
    }
    
    /**
     * Menampilkan detail satu aset.
     */
    public function show(Asset $asset)
    {
        $asset->load(['user', 'category', 'company', 'history.user']);
        $urlToScan = route('assets.public.show', $asset->id);
        $qrCode = QrCode::size(250)->generate($urlToScan);
        return view('assets.show', compact('asset', 'qrCode'));
    }

    /**
     * Menampilkan halaman edit aset.
     */
    public function edit(Asset $asset)
    {
        $asset->load(['user', 'category', 'company']);
        return view('assets.edit', [
            'asset' => $asset,
            'users' => User::all(),
            'categories' => Category::with('units')->get(),
            'companies' => Company::all(),
        ]);
    }

    /**
     * Memperbarui data aset di database.
     */
    public function update(Request $request, Asset $asset)
    {
        // Untuk saat ini, kita gunakan logika update yang lama yang sudah stabil
        $request->validate([
            'nama_barang' => 'required|string',
            'serial_number' => 'nullable|string|max:255|unique:assets,serial_number,' . $asset->id,
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
        ]);
        
        $updateData = $request->except(['_token', '_method', 'code_asset', 'category_id', 'company_id']);
        
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

        return redirect()->route('assets.index')->with('success', 'Data aset berhasil diperbarui.');
    }

    /**
     * Menghapus aset dari database.
     */
    public function destroy(Asset $asset)
    {
        $asset->history()->delete();
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Aset dan semua riwayatnya berhasil dihapus.');
    }
    
    /**
     * Menampilkan halaman publik untuk satu aset.
     */
    public function publicShow(Asset $asset)
    {
        $asset->load(['user', 'category', 'company', 'history.user']);
        return view('assets.public-show', compact('asset'));
    }

    /**
     * Mengambil satuan (unit) berdasarkan kategori untuk form dinamis.
     */
    public function getUnits(Category $category)
    {
        return response()->json($category->units);
    }
    
    /**
     * Menangani proses impor dari file Excel.
     */
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);
        Excel::import(new AssetsImport, $request->file('file'));
        return redirect()->route('assets.index')->with('success', 'Data aset berhasil diimpor.');
    }

    /**
     * Menangani pencetakan label aset.
     */
    public function print(Request $request)
    {
        $assetIds = $request->query('ids');
        if ($assetIds && is_array($assetIds) && count($assetIds) > 0) {
            $assets = Asset::with('user')->whereIn('id', $assetIds)->get();
        } else {
            return redirect()->route('assets.index')->with('error', 'Tidak ada aset yang dipilih untuk dicetak.');
        }
        return view('assets.print', compact('assets'));
    }
    
    /**
     * Menangani proses ekspor data ke file Excel.
     */
    public function export(Request $request)
    {
        // Ambil ID aset yang dipilih dari query string, jika ada.
        $assetIds = $request->query('ids');

        // Ambil term pencarian dari query string, jika ada.
        $searchTerm = $request->query('search');

        // Tentukan nama file secara dinamis
        $fileName = 'assets_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        // Buat instance AssetsExport, kirimkan parameter search dan ids
        // lalu langsung trigger download filenya.
        return Excel::download(new AssetsExport($searchTerm, $assetIds), $fileName);
    }
}