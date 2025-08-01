<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;
use App\Imports\AssetsImport;
use App\Exports\AssetsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AssetController extends Controller
{
    // ... (semua properti dan method dari atas hingga 'store' tetap sama)
    private $assetCategories = [
        'ELEC' => 'Elektronik',
        'FURN' => 'Furniture',
        'VEHI' => 'Kendaraan',
        'OFFI' => 'Peralatan Kantor Lainnya',
    ];

    private $companyCodes = [
        'JG'  => 'Jhonlin Group',
        'JMT' => 'Jhonlin Marine Trans',
        'JB'  => 'Jhonlin Baratama',
        'JAR' => 'Jhonlin Agro Raya',
        'JML' => 'Jhonlin Migas Lestari',
    ];

    private function generateAssetCode(string $itemName, string $categoryCode, string $companyCode, int $assetId): string
    {
        $itemAbbreviation = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $itemName), 0, 3));
        $paddedId = str_pad($assetId, 6, '0', STR_PAD_LEFT);
        return "{$itemAbbreviation}/{$categoryCode}/{$companyCode}/{$paddedId}";
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

    public function index(Request $request)
    {
        $search = $request->input('search');
        $assets = Asset::with('user')
            ->when($search, function ($query, $searchTerm) {
                return $query->where('code_asset', 'like', "%{$searchTerm}%")
                             ->orWhere('nama_barang', 'like', "%{$searchTerm}%")
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
        return view('assets.create', [
            'users' => User::all(),
            'assetCategories' => $this->assetCategories,
            'companyCodes' => $this->companyCodes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'asset_category' => 'required|string',
            'company_code' => 'required|string',
            'spec_input_type' => 'required|in:detailed,manual',
            'spesifikasi_manual' => 'required_if:spec_input_type,manual|nullable|string',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
            'serial_number' => 'nullable|string|max:255|unique:assets,serial_number',
        ]);

        $data = $request->except(['_token', 'asset_category', 'company_code']);

        if ($request->filled('tanggal_pembelian')) {
            $data['thn_pembelian'] = Carbon::parse($request->tanggal_pembelian)->format('Y');
        }

        if ($data['spec_input_type'] === 'manual') {
            $data['processor'] = $data['memory_ram'] = $data['hdd_ssd'] = $data['graphics'] = $data['lcd'] = null;
        } else {
            $data['spesifikasi_manual'] = null;
        }

        $userId = $this->getUserIdFromRequest($request);
        $data['user_id'] = $userId;
        
        $data['code_asset'] = 'PENDING';
        $asset = Asset::create($data);

        $asset->code_asset = $this->generateAssetCode($request->nama_barang, $request->asset_category, $request->company_code, $asset->id);
        $asset->save();

        if ($userId) {
            $asset->history()->create([
                'user_id' => $userId,
                'tanggal_mulai' => now()
            ]);
        }

        return redirect()->route('assets.index')->with('success', 'Aset baru berhasil ditambahkan dengan kode: ' . $asset->code_asset);
    }

    public function show(Asset $asset)
    {
        try {
            $asset->load(['user', 'history.user']);
            $urlToScan = route('assets.public.show', $asset->id);
            $qrCode = QrCode::size(250)->generate($urlToScan);
            return view('assets.show', compact('asset', 'qrCode'));
        } catch (\Throwable $e) {
            // Tampilkan pesan error detail jika terjadi masalah
            return response("Terjadi error: " . $e->getMessage() . " di file: " . $e->getFile() . " pada baris: " . $e->getLine(), 500);
        }
    }

    /**
     * PERUBAHAN UTAMA: Menambahkan try...catch untuk menangkap error
     */
    public function publicShow(Asset $asset)
    {
        try {
            // Kode ini akan mencoba memuat semua relasi yang dibutuhkan
            $asset->load(['user', 'history.user']);
            
            // Jika berhasil, tampilkan halaman seperti biasa
            return view('assets.public-show', compact('asset'));

        } catch (\Throwable $e) {
            // Jika GAGAL, hentikan semua proses dan tampilkan pesan error yang jelas
            // Ini akan memberi tahu kita apa masalah sebenarnya
            return response("Terjadi error: " . $e->getMessage() . " di file: " . $e->getFile() . " pada baris: " . $e->getLine(), 500);
        }
    }

    // ... (method edit, update, destroy, import, print, export tetap sama)
    public function edit(Asset $asset)
    {
        return view('assets.edit', [
            'asset' => $asset,
            'users' => User::all(),
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        $request->validate([
            'nama_barang' => 'required|string',
            'serial_number' => 'nullable|string|max:255|unique:assets,serial_number,' . $asset->id,
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
        ]);
        
        $updateData = $request->except(['_token', '_method', 'code_asset']);
        
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
                $asset->history()->create([
                    'user_id' => $newUserId,
                    'tanggal_mulai' => now()
                ]);
            }
        }

        return redirect()->route('assets.index')->with('success', 'Data aset berhasil diperbarui.');
    }

    public function destroy(Asset $asset)
    {
        $asset->history()->delete();
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Aset dan semua riwayatnya berhasil dihapus.');
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
        } else {
            return redirect()->route('assets.index')->with('error', 'Tidak ada aset yang dipilih untuk dicetak.');
        }
        return view('assets.print', compact('assets'));
    }

    public function export(Request $request)
    {
        $assetIds = $request->query('ids');
        $search = $request->query('search');
        $filename = 'aset_data_' . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new AssetsExport($search, $assetIds), $filename);
    }

    public function downloadPDF(Asset $asset)
    {
        $asset->load('user');
        $pdf = Pdf::loadView('assets.pdf', compact('asset'));
        $filename = 'ASET-' . str_replace('/', '-', $asset->code_asset) . '.pdf';
        return $pdf->download($filename);
    }
}
