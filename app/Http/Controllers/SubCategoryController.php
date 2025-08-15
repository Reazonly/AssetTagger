<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('subCategories')->latest()->paginate(15);
        return view('masters.sub-categories.index', compact('categories'));
    }

    public function show(Category $category)
    {
        // --- PERBAIKAN DI SINI ---
        // Menambahkan .latest() untuk mengurutkan dari yang terbaru.
        $subCategories = $category->subCategories()->latest()->paginate(15);
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
            'input_type' => ['required', Rule::in(['none', 'merk', 'tipe', 'merk_dan_tipe'])],
            'spec_fields' => 'nullable|array',
            'spec_fields.*' => 'nullable|string|max:255',
        ]);

        $specFields = !empty($validated['spec_fields']) 
            ? array_filter($validated['spec_fields']) 
            : null;

        $category->subCategories()->create([
            'name' => $validated['name'],
            'input_type' => $validated['input_type'],
            'spec_fields' => $specFields,
        ]);

        return redirect()->route('master-data.sub-categories.show', $category)
                         ->with('success', 'Sub-Kategori baru berhasil ditambahkan.');
    }

    public function edit(SubCategory $subCategory)
    {
        return view('masters.sub-categories.edit', compact('subCategory'));
    }

    public function update(Request $request, SubCategory $subCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'input_type' => ['required', Rule::in(['none', 'merk', 'tipe', 'merk_dan_tipe'])],
            'spec_fields' => 'nullable|array',
            'spec_fields.*' => 'nullable|string|max:255',
        ]);

        $specFields = !empty($validated['spec_fields']) 
            ? array_filter($validated['spec_fields']) 
            : null;
        
        $subCategory->update([
            'name' => $validated['name'],
            'input_type' => $validated['input_type'],
            'spec_fields' => $specFields,
        ]);

        return redirect()->route('master-data.sub-categories.show', $subCategory->category_id)
                         ->with('success', 'Sub-Kategori berhasil diperbarui.');
    }

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
