<?php

namespace App\Imports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Illuminate\Support\Str;

class CategoryImport implements ToModel, WithHeadingRow, WithUpserts
{
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

     
        $name = $this->findValueByAliases($normalizedRow, ['nama_kategori', 'kategori', 'nama']);
        $code = $this->findValueByAliases($normalizedRow, ['kode_kategori', 'kode']);

        
        if (!$name || !$code) {
            return null;
        }

        return new Category([
            'name' => $name,
            'code' => $code,
            'slug' => Str::slug($name),
        ]);
    }

    public function uniqueBy()
    {
        return 'code';
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