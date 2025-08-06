<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query();
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('code', 'like', "%{$searchTerm}%");
        }
        $companies = $query->latest()->paginate(15);
        return view('masters.companies.index', compact('companies'));
    }

    public function create()
    {
        return view('masters.companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:companies,code',
        ]);
        Company::create($validated);
        return redirect()->route('master-data.companies.index')->with('success', 'Perusahaan baru berhasil ditambahkan.');
    }

    public function edit(Company $company)
    {
        return view('masters.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:companies,code,' . $company->id,
        ]);
        $company->update($validated);
        return redirect()->route('master-data.companies.index')->with('success', 'Data perusahaan berhasil diperbarui.');
    }

    public function destroy(Company $company)
    {
        if ($company->assets()->count() > 0) {
            return back()->with('error', 'Perusahaan tidak dapat dihapus karena masih terhubung dengan data aset.');
        }
        $company->delete();
        return redirect()->route('master-data.companies.index')->with('success', 'Data perusahaan berhasil dihapus.');
    }
}