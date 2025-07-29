<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;
use App\Imports\AssetsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AssetController extends Controller
{
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
        $request->validate(['code_asset' => 'required|string|unique:assets,code_asset', 'nama_barang' => 'required|string']);
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
        $request->validate(['code_asset' => 'required|string|unique:assets,code_asset,' . $asset->id, 'nama_barang' => 'required|string']);
        $oldUserId = $asset->user_id;
        $newUserId = $this->getUserIdFromRequest($request);
        $request->merge(['user_id' => $newUserId]);
        $asset->update($request->all());
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
        $assets = $request->has('id') ? Asset::where('id', $request->id)->get() : Asset::all();
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
