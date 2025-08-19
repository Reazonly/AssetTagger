<?php

namespace App\Http\Controllers;

use App\Models\AssetUser;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Imports\AssetUserImport;
use Maatwebsite\Excel\Facades\Excel;


class AssetUserController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetUser::with('company');

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('nama', 'like', "%{$searchTerm}%")
                  ->orWhere('jabatan', 'like', "%{$searchTerm}%")
                  ->orWhere('departemen', 'like', "%{$searchTerm}%")
                  ->orWhereHas('company', function ($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  });
        }
        $assetUsers = $query->latest()->paginate(15);
        return view('masters.asset-users.index', compact('assetUsers'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        return view('masters.asset-users.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'departemen' => 'nullable|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
        ]);
        AssetUser::create($validated);
        return redirect()->route('master-data.asset-users.index')->with('success', 'Pengguna aset baru berhasil ditambahkan.');
    }

    public function edit(AssetUser $assetUser)
    {
        $companies = Company::orderBy('name')->get();
        return view('masters.asset-users.edit', compact('assetUser', 'companies'));
    }

    public function update(Request $request, AssetUser $assetUser)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'departemen' => 'nullable|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
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

    public function import(Request $request)
{
    $request->validate(['file' => 'required|mimes:xls,xlsx,csv']);
    Excel::import(new AssetUserImport, $request->file('file'));
    return redirect()->route('master-data.asset-users.index')->with('success', 'Data pengguna aset berhasil diimpor.');
}

}
