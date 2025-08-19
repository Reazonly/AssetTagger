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

    public function model(array $row)
    {
        $normalizedRow = $this->normalizeRowKeys($row);

        $inputType = $normalizedRow['tipe_input'] ?? 'none';
        $formattedInputType = strtolower(str_replace(' ', '_', $inputType));


        if (!in_array($formattedInputType, ['merk', 'tipe', 'merk_dan_tipe', 'none'])) {
            $formattedInputType = 'none';
        }
       

        return new SubCategory([
            'category_id' => $this->category->id,
            'name'        => $normalizedRow['nama_sub_kategori'],
            'slug'        => Str::slug($normalizedRow['nama_sub_kategori']),
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
