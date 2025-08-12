<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    /**
     * Menampilkan daftar KATEGORI sebagai halaman utama.
     */
    public function index()
    {
        $categories = Category::withCount('subCategories')->latest()->paginate(15);
        return view('masters.sub-categories.index', compact('categories'));
    }

    /**
     * Menampilkan daftar SUB-KATEGORI untuk satu Kategori spesifik.
     */
    public function show(Category $category)
    {
        $subCategories = $category->subCategories()->paginate(15);
        return view('masters.sub-categories.show', compact('category', 'subCategories'));
    }

    /**
     * Menampilkan form untuk membuat Sub-Kategori baru.
     */
    public function create(Category $category)
    {
        return view('masters.sub-categories.create', compact('category'));
    }

    /**
     * Menyimpan Sub-Kategori baru.
     */
    public function store(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'spec_fields' => 'nullable|array',
            'spec_fields.*' => 'nullable|string|max:255', // Validasi setiap item di array
        ]);

        // Filter field spesifikasi yang kosong
        $specFields = !empty($validated['spec_fields']) 
            ? array_filter($validated['spec_fields']) 
            : null;

        $category->subCategories()->create([
            'name' => $validated['name'],
            'spec_fields' => $specFields,
        ]);

        return redirect()->route('master-data.sub-categories.show', $category)
                         ->with('success', 'Sub-Kategori baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit Sub-Kategori.
     */
    public function edit(SubCategory $subCategory)
    {
        return view('masters.sub-categories.edit', compact('subCategory'));
    }

    /**
     * Memperbarui Sub-Kategori.
     */
    public function update(Request $request, SubCategory $subCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'spec_fields' => 'nullable|array',
            'spec_fields.*' => 'nullable|string|max:255',
        ]);

        $specFields = !empty($validated['spec_fields']) 
            ? array_filter($validated['spec_fields']) 
            : null;
        
        $subCategory->update([
            'name' => $validated['name'],
            'spec_fields' => $specFields,
        ]);

        return redirect()->route('master-data.sub-categories.show', $subCategory->category_id)
                         ->with('success', 'Sub-Kategori berhasil diperbarui.');
    }

    /**
     * Menghapus Sub-Kategori.
     */
    public function destroy(SubCategory $subCategory)
    {
        if ($subCategory->assets()->count() > 0) {
            return back()->with('error', 'Sub-Kategori tidak dapat dihapus karena masih digunakan oleh data aset.');
        }
        $categoryId = $subCategory->category_id;
        $subCategory->delete();
        return redirect()->route('master-data.sub-categories.show', $categoryId)
                         ->with('success', 'Sub-Kategori berhasil dihapus.');
    }
}
