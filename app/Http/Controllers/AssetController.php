<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;
use App\Imports\AssetsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class AssetController extends Controller
{
    // ... (method index, create, show, dll tidak berubah) ...
    public function index(Request $request)
    {
        $search = $request->input('search');

        $assets = Asset::with('user')
            ->when($search, function ($query, $searchTerm) {
                return $query->where('code_asset', 'like', "%{$searchTerm}%")
                             ->orWhere('nama_barang', 'like', "%{$searchTerm}%")
                             ->orWhere('serial_number', 'like', "%{$searchTerm}%")
                             ->orWhereHas('user', function ($subQuery) use ($searchTerm) {
                                 $subQuery->where('nama_pengguna', 'like', "%{$searchTerm}%");
                             });
            })
            ->latest()
            ->paginate(15);

        return view('assets.index', compact('assets', 'search'));
    }

    public function create()
    {
        $users = User::all();
        return view('assets.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code_asset' => 'required|string|unique:assets,code_asset',
            'nama_barang' => 'required|string',
        ]);

        // Logika untuk memisahkan tanggal dan tahun dari input form
        if ($request->filled('tanggal_pembelian')) {
            $request->merge([
                'thn_pembelian' => Carbon::parse($request->tanggal_pembelian)->format('Y')
            ]);
        }

        $userId = $this->getUserIdFromRequest($request);
        $request->merge(['user_id' => $userId]);
        $asset = Asset::create($request->all());

        if ($userId) {
            $asset->history()->create(['user_id' => $userId]);
        }

        return redirect()->route('assets.index')->with('success', 'Aset baru berhasil ditambahkan.');
    }

    public function show(Asset $asset)
    {
        $asset->load(['user', 'history.user']);
        $urlToScan = route('assets.public.show', $asset, true);
        $qrCode = QrCode::size(250)->generate($urlToScan);
        return view('assets.show', compact('asset', 'qrCode'));
    }

    public function publicShow(Asset $asset)
    {
        $asset->load(['user', 'history.user']);
        return view('assets.public-show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $asset->load('history.user');
        $users = User::all();
        return view('assets.edit', compact('asset', 'users'));
    }

    public function update(Request $request, Asset $asset)
    {
        $request->validate([
            'code_asset' => 'required|string|unique:assets,code_asset,' . $asset->id,
            'nama_barang' => 'required|string',
        ]);
        
        // Menyiapkan data yang akan diupdate
        $updateData = $request->all();

        // Logika baru yang lebih kuat untuk menangani tanggal
        if ($request->filled('tanggal_pembelian')) {
            // Jika tanggal diisi, simpan tanggal dan ekstrak tahunnya
            $updateData['thn_pembelian'] = \Carbon\Carbon::parse($request->tanggal_pembelian)->format('Y');
        } else {
            // Jika tanggal dikosongkan, pastikan kedua kolom di database juga kosong
            $updateData['tanggal_pembelian'] = null;
            $updateData['thn_pembelian'] = null;
        }
        
        $oldUserId = $asset->user_id;
        $newUserId = $this->getUserIdFromRequest($request);
        
        $updateData['user_id'] = $newUserId;
        
        // Lakukan update menggunakan data yang sudah disiapkan
        $asset->update($updateData);

        if ($oldUserId != $newUserId) {
            if ($oldUserId) {
                $asset->history()->where('user_id', $oldUserId)->whereNull('tanggal_selesai')->update(['tanggal_selesai' => now()]);
            }
            if ($newUserId) {
                $asset->history()->create(['user_id' => $newUserId]);
            }
        }

        return redirect()->route('assets.index')->with('success', 'Data aset berhasil diperbarui.');
    }
    
    // ... (method destroy, import, print, getUserIdFromRequest tidak berubah) ...
    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Aset berhasil dihapus.');
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
            $assets = Asset::whereIn('id', $assetIds)->get();
        } else {
            $assets = Asset::all();
        }
        return view('assets.print', compact('assets'));
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
}
