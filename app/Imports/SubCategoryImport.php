<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\SubCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Illuminate\Support\Str;

class SubCategoryImport implements ToModel, WithHeadingRow, WithUpserts
{
    protected $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }
    
    private function findValueByAliases(array $row, array $aliases): ?string
    {
        foreach ($aliases as $alias) {
            if (isset($row[$alias]) && !empty($row[$alias])) {
                return $row[$alias];
            }
        }
        return null;
    }

    public function model(array $row)
    {
        $normalizedRow = $this->normalizeRowKeys($row);

       
        $name = $this->findValueByAliases($normalizedRow, ['nama_sub_kategori', 'sub_kategori', 'nama']);
        $inputType = $this->findValueByAliases($normalizedRow, ['tipe_input', 'input']) ?? 'none';
        
        
        if (!$name) {
            return null;
        }

        $formattedInputType = strtolower(str_replace(' ', '_', $inputType));
        if (!in_array($formattedInputType, ['merk', 'tipe', 'merk_dan_tipe', 'none'])) {
            $formattedInputType = 'none';
        }
       
        return new SubCategory([
            'category_id' => $this->category->id,
            'name'        => $name,
            'slug'        => Str::slug($name),
            'input_type'  => $formattedInputType, 
        ]);
    }

    public function uniqueBy()
    {
        return 'name';
    }

    private function normalizeRowKeys(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $newKey = Str::snake(strtolower($key));
            $normalized[$newKey] = $value;
        }
        return $normalized;
    }
}