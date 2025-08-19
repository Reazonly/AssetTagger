<?php

namespace App\Imports;

use App\Models\AssetUser;
use App\Models\Company;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Illuminate\Support\Str; // <-- Tambahkan ini

class AssetUserImport implements ToModel, WithHeadingRow, WithUpserts
{
    public function model(array $row)
    {
        // Normalisasi key dari row
        $normalizedRow = $this->normalizeRowKeys($row);

        $company = !empty($normalizedRow['kode_perusahaan'])
            ? Company::where('code', $normalizedRow['kode_perusahaan'])->first()
            : null;

        return new AssetUser([
            'nama'       => $normalizedRow['nama'],
            'jabatan'    => $normalizedRow['jabatan'] ?? null,
            'departemen' => $normalizedRow['departemen'] ?? null,
            'company_id' => optional($company)->id,
        ]);
    }

    public function uniqueBy()
    {
        return 'nama';
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
