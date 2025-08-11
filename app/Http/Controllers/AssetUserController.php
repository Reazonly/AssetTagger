<?php

namespace App\Http\Controllers;

use App\Models\AssetUser;
use Illuminate\Http\Request;

class AssetUserController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetUser::query();
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('nama', 'like', "%{$searchTerm}%")
                  ->orWhere('jabatan', 'like', "%{$searchTerm}%")
                  ->orWhere('departemen', 'like', "%{$searchTerm}%");
        }
        $assetUsers = $query->latest()->paginate(15);
        return view('masters.asset-users.index', compact('assetUsers'));
    }

    public function create()
    {
        return view('masters.asset-users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'departemen' => 'nullable|string|max:255',
        ]);
        AssetUser::create($validated);
        return redirect()->route('master-data.asset-users.index')->with('success', 'Pengguna aset baru berhasil ditambahkan.');
    }

    public function edit(AssetUser $assetUser)
    {
        return view('masters.asset-users.edit', compact('assetUser'));
    }

    public function update(Request $request, AssetUser $assetUser)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'departemen' => 'nullable|string|max:255',
        ]);
        $assetUser->update($validated);
        return redirect()->route('master-data.asset-users.index')->with('success', 'Data pengguna aset berhasil diperbarui.');
    }

    public function destroy(AssetUser $assetUser)
    {
        if ($assetUser->assets()->count() > 0) {
            return back()->with('error', 'Pengguna tidak dapat dihapus karena masih terhubung dengan data aset.');
        }
        $assetUser->delete();
        return redirect()->route('master-data.asset-users.index')->with('success', 'Pengguna aset berhasil dihapus.');
    }
}