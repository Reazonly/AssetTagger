<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Imports\CategoryImport; 
use Maatwebsite\Excel\Facades\Excel; 

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('code', 'like', "%{$searchTerm}%");
        }
        $categories = $query->latest()->paginate(15);
        return view('masters.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('masters.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:categories,code',
        ]);
        Category::create($validated);
        return redirect()->route('master-data.categories.index')->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('masters.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:categories,code,' . $category->id,
        ]);
        $category->update($validated);
        return redirect()->route('master-data.categories.index')->with('success', 'Data kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->assets()->count() > 0) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh data aset.');
        }
        $category->delete();
        return redirect()->route('master-data.categories.index')->with('success', 'Data kategori berhasil dihapus.');
    }

    public function import(Request $request)
{
    $request->validate(['file' => 'required|mimes:xls,xlsx,csv']);
    Excel::import(new CategoryImport, $request->file('file'));
    return redirect()->route('master-data.categories.index')->with('success', 'Data kategori berhasil diimpor.');
}
}
