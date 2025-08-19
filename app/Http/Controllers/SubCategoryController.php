<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Imports\SubCategoryImport; // Tambahkan ini di atas
use Maatwebsite\Excel\Facades\Excel; // Tambahkan ini di atas


class SubCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('subCategories')->with('subCategories');

        // Logika pencarian untuk halaman index
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('name', 'like', "%{$searchTerm}%")
                  ->orWhereHas('subCategories', function ($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  });
        }

        $categories = $query->latest()->paginate(10);
        return view('masters.sub-categories.index', compact('categories'));
    }

    public function show(Request $request, Category $category)
    {
        $query = $category->subCategories();

        // Logika pencarian untuk halaman show
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('name', 'like', "%{$searchTerm}%");
        }

        $subCategories = $query->latest()->paginate(15);
        return view('masters.sub-categories.show', compact('category', 'subCategories'));
    }

    public function create(Category $category)
    {
        return view('masters.sub-categories.create', compact('category'));
    }

    public function store(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'input_type' => 'required|string|in:merk,tipe,merk_dan_tipe,none',
        ]);

        $category->subCategories()->create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'input_type' => $validated['input_type'],
        ]);

        return redirect()->route('master-data.sub-categories.show', $category->id)
                         ->with('success', 'Sub-kategori baru berhasil ditambahkan.');
    }

    public function edit(SubCategory $subCategory)
    {
        return view('masters.sub-categories.edit', compact('subCategory'));
    }

    public function update(Request $request, SubCategory $subCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'input_type' => 'required|string|in:merk,tipe,merk_dan_tipe,none',
        ]);

        $subCategory->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'input_type' => $validated['input_type'],
        ]);

        return redirect()->route('master-data.sub-categories.show', $subCategory->category_id)
                         ->with('success', 'Sub-kategori berhasil diperbarui.');
    }

    public function destroy(SubCategory $subCategory)
    {
        $categoryId = $subCategory->category_id;
        $subCategory->delete();
        return redirect()->route('master-data.sub-categories.show', $categoryId)
                         ->with('success', 'Sub-kategori berhasil dihapus.');
    }

    public function import(Request $request, Category $category)
    {
        $request->validate(['file' => 'required|mimes:xls,xlsx,csv']);
        
        Excel::import(new SubCategoryImport($category), $request->file('file')); 
        
        return redirect()->route('master-data.sub-categories.show', $category->id)
                         ->with('success', 'Data sub-kategori berhasil diimpor.');
    }
}