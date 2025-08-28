<?php

namespace App\Imports;

use App\Models\AssetUser;
use App\Models\Company;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Illuminate\Support\Str;

class AssetUserImport implements ToModel, WithHeadingRow, WithUpserts
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

        $company = null;
        $companyName = $this->findValueByAliases($normalizedRow, ['perusahaan', 'nama_perusahaan']);
        if ($companyName) {
            $cleanName = $this->cleanCompanyName($companyName);
            $company = Company::firstOrCreate(
                ['name' => $cleanName], 
                ['code' => $this->generateUniqueCompanyCode($cleanName)] 
            );
        }

      
        $nama = $this->findValueByAliases($normalizedRow, ['nama', 'nama_pengguna']);
        
       
        if (!$nama) {
            return null;
        }

        return new AssetUser([
            'nama'       => $nama,
            'jabatan'    => $this->findValueByAliases($normalizedRow, ['jabatan', 'posisi']) ?? null,
            'departemen' => $this->findValueByAliases($normalizedRow, ['departemen']) ?? null,
            'company_id' => optional($company)->id,
        ]);
    }

    public function uniqueBy()
    {
        return 'nama';
    }

    private function cleanCompanyName(string $companyName): string
    {
        return trim(preg_replace('/^(pt\.?|cv\.?)\s*/i', '', $companyName));
    }

    private function generateUniqueCompanyCode(string $companyName): string
    {
        $cleanName = $this->cleanCompanyName($companyName);
        $words = explode(' ', $cleanName);
        
        $code = '';
        if (count($words) > 1) {
            $code = strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
        } else {
            $code = strtoupper(substr($cleanName, 0, 2));
        }

        if (Company::where('code', $code)->exists()) {
            if (count($words) > 1 && strlen($words[1]) > 1) {
                $altCode = strtoupper(substr($words[0], 0, 1) . substr($words[1], 1, 1));
                if (!Company::where('code', $altCode)->exists()) {
                    return $altCode;
                }
            }

            $i = 2;
            while (Company::where('code', $code . $i)->exists()) {
                $i++;
            }
            $code = $code . $i;
        }
        
        return substr($code, 0, 10); 
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