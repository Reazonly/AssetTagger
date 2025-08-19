<?php

namespace App\Imports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Illuminate\Support\Str;

class CategoryImport implements ToModel, WithHeadingRow, WithUpserts
{
    public function model(array $row)
    {
        // Normalisasi key dari row
        $normalizedRow = $this->normalizeRowKeys($row);

        return new Category([
            'name' => $normalizedRow['nama_kategori'],
            'code' => $normalizedRow['kode_kategori'],
            'slug' => Str::slug($normalizedRow['nama_kategori']),
        ]);
    }

    public function uniqueBy()
    {
        return 'code';
    }

    /**
     * Mengubah key dari heading yang mudah dibaca menjadi format snake_case.
     */
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
