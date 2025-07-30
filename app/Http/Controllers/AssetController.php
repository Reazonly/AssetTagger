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
    /**
     * Menampilkan daftar semua aset dengan fungsionalitas pencarian dan paginasi.
     */
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

    /**
     * Menampilkan form untuk membuat aset baru.
     */
    public function create()
    {
        $users = User::all();
        return view('assets.create', compact('users'));
    }

    /**
     * Menyimpan aset baru ke dalam database dan membuat catatan riwayat awal.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code_asset' => 'required|string|unique:assets,code_asset',
            'nama_barang' => 'required|string',
        ]);

        $assetData = $request->all();

        // Logika untuk memisahkan tanggal dan tahun dari input form
        if ($request->filled('tanggal_pembelian')) {
            $assetData['thn_pembelian'] = Carbon::parse($request->tanggal_pembelian)->format('Y');
        }

        // Dapatkan atau buat pengguna dan tetapkan user_id
        $userId = $this->getUserIdFromRequest($request);
        $assetData['user_id'] = $userId;
        
        // Buat aset
        $asset = Asset::create($assetData);

        // Jika ada pengguna yang ditugaskan, buat catatan riwayat dengan tanggal mulai
        if ($userId) {
            $asset->history()->create([
                'user_id' => $userId,
                'tanggal_mulai' => now() // Catat waktu saat aset ditugaskan
            ]);
        }

        return redirect()->route('assets.index')->with('success', 'Aset baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail spesifik dari sebuah aset, termasuk QR Code.
     */
    public function show(Asset $asset)
    {
        $asset->load(['user', 'history.user']);
        // URL untuk halaman publik yang akan di-scan
        $urlToScan = route('assets.public.show', $asset, true);
        $qrCode = QrCode::size(250)->generate($urlToScan);
        return view('assets.show', compact('asset', 'qrCode'));
    }

    /**
     * Menampilkan halaman publik untuk detail aset (hasil dari scan QR).
     */
    public function publicShow(Asset $asset)
    {
        $asset->load(['user', 'history.user']);
        return view('assets.public-show', compact('asset'));
    }

    /**
     * Menampilkan form untuk mengedit data aset.
     */
    public function edit(Asset $asset)
    {
        $asset->load('history.user');
        $users = User::all();
        return view('assets.edit', compact('asset', 'users'));
    }

    /**
     * Memperbarui data aset di database dan mengelola riwayat pengguna.
     */
    public function update(Request $request, Asset $asset)
    {
        $request->validate([
            'code_asset' => 'required|string|unique:assets,code_asset,' . $asset->id,
            'nama_barang' => 'required|string',
        ]);
        
        $updateData = $request->all();

        // Logika baru untuk menangani tanggal pembelian
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

        // Periksa apakah pengguna telah berubah untuk memperbarui riwayat
        if ($oldUserId != $newUserId) {
            // Jika ada pengguna lama, akhiri riwayatnya
            if ($oldUserId) {
                $asset->history()->where('user_id', $oldUserId)->whereNull('tanggal_selesai')->update(['tanggal_selesai' => now()]);
            }
            // Jika ada pengguna baru, buat riwayat baru untuknya
            if ($newUserId) {
                $asset->history()->create([
                    'user_id' => $newUserId,
                    'tanggal_mulai' => now() // Catat waktu saat aset ditugaskan ke pengguna baru
                ]);
            }
        }

        return redirect()->route('assets.index')->with('success', 'Data aset berhasil diperbarui.');
    }
    
    /**
     * Menghapus aset dari database.
     */
    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Aset berhasil dihapus.');
    }

    /**
     * Mengimpor data aset dari file Excel/CSV.
     */
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);
        Excel::import(new AssetsImport, $request->file('file'));
        return redirect()->route('assets.index')->with('success', 'Data aset berhasil diimpor.');
    }
    
    /**
     * Menyiapkan halaman untuk mencetak label QR code.
     */
    public function print(Request $request)
    {
        $assetIds = $request->query('ids');
        if ($assetIds && is_array($assetIds) && count($assetIds) > 0) {
            $assets = Asset::whereIn('id', $assetIds)->get();
        } else {
            // Jika tidak ada ID yang dipilih, bisa kembali ke halaman index dengan pesan
            return redirect()->route('assets.index')->with('error', 'Tidak ada aset yang dipilih untuk dicetak.');
        }
        return view('assets.print', compact('assets'));
    }

    /**
     * Helper function untuk mendapatkan ID pengguna dari request.
     * Membuat pengguna baru jika 'new_user_name' diisi.
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
}
