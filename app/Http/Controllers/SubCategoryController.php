<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Imports\SubCategoryImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SubCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('subCategories')->latest()->paginate(15);
        return view('masters.sub-categories.index', compact('categories'));
    }

    public function show(Category $category)
    {
        $subCategories = $category->subCategories()->latest()->paginate(15);
        return view('masters.sub-categories.show', compact('category', 'subCategories'));
    }

    public function create(Category $category)
    {
        return view('masters.sub-categories.create', compact('category'));
    }

    private function processSpecFields(Request $request): array
    {
        $specFields = [];
        if ($request->has('spec_fields')) {
            foreach ($request->spec_fields as $field) {
                // Hanya simpan field yang namanya diisi
                if (!empty($field['name'])) {
                    $specFields[] = [
                        'name' => $field['name'],
                        'type' => $field['type'] ?? 'text', // Default ke 'text' jika tipe tidak ada
                    ];
                }
            }
        }
        return $specFields;
    }

    public function store(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'input_type' => 'required|in:merk,tipe,merk_dan_tipe,none',
            'spec_fields' => 'nullable|array',
            'spec_fields.*.name' => 'nullable|string|max:255',
            'spec_fields.*.type' => 'nullable|string|in:text,number,textarea',
        ]);

        $subCategory = new SubCategory([
            'category_id' => $category->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'input_type' => $validated['input_type'],
            'spec_fields' => $this->processSpecFields($request),
        ]);
        
        $subCategory->save();

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
            'input_type' => 'required|in:merk,tipe,merk_dan_tipe,none',
            'spec_fields' => 'nullable|array',
            'spec_fields.*.name' => 'nullable|string|max:255',
            'spec_fields.*.type' => 'nullable|string|in:text,number,textarea',
        ]);

        $subCategory->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'input_type' => $validated['input_type'],
            'spec_fields' => $this->processSpecFields($request),
        ]);

        return redirect()->route('master-data.sub-categories.show', $subCategory->category_id)
                         ->with('success', 'Sub-kategori berhasil diperbarui.');
    }

    public function destroy(SubCategory $subCategory)
    {
        if ($subCategory->assets()->count() > 0) {
            return back()->with('error', 'Sub-kategori tidak dapat dihapus karena masih digunakan oleh data aset.');
        }

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