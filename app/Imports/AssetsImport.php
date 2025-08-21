<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Company;
use App\Models\AssetUser;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AssetsImport implements ToCollection, WithHeadingRow, WithValidation
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            $normalizedRow = $this->normalizeRowKeys($row->toArray());

            $category = !empty($normalizedRow['kategori']) 
                ? Category::firstOrCreate(
                    ['name' => $normalizedRow['kategori']],
                    ['code' => substr(strtoupper(Str::slug($normalizedRow['kategori'])), 0, 4), 'slug' => Str::slug($normalizedRow['kategori'])]
                )
                : null;
            
            if (!$category) continue;

            $subCategory = ($category && !empty($normalizedRow['sub_kategori'])) 
                ? SubCategory::firstOrCreate(
                    ['name' => $normalizedRow['sub_kategori'], 'category_id' => $category->id]
                ) 
                : null;
            
            $company = null;
            if (!empty($normalizedRow['perusahaan_pemilik'])) {
                $companyName = $this->cleanCompanyName($normalizedRow['perusahaan_pemilik']);
                $companyCode = $this->generateCompanyCode($companyName);
                $company = Company::firstOrCreate(
                    ['code' => $companyCode],
                    ['name' => $companyName]
                );
            }
            
            $assetUser = null;
            if (!empty($normalizedRow['pengguna_aset'])) {
                $userCompany = null;
                if (!empty($normalizedRow['perusahaan_pengguna'])) {
                    $userCompanyName = $this->cleanCompanyName($normalizedRow['perusahaan_pengguna']);
                    $userCompanyCode = $this->generateCompanyCode($userCompanyName);
                    $userCompany = Company::firstOrCreate(
                        ['code' => $userCompanyCode],
                        ['name' => $userCompanyName]
                    );
                }

                $assetUser = AssetUser::updateOrCreate(
                    ['nama' => $normalizedRow['pengguna_aset']],
                    [
                        'jabatan' => $normalizedRow['jabatan_pengguna'] ?? null, 
                        'departemen' => $normalizedRow['departemen_pengguna'] ?? null,
                        'company_id' => optional($userCompany)->id
                    ]
                );
            }

           
            $tanggal_pembelian = null;
            try {
                if (!empty($normalizedRow['tahun_pembelian']) && !empty($normalizedRow['bulan_pembelian']) && !empty($normalizedRow['tanggal_pembelian'])) {
                    $bulanAngka = $this->getMonthNumber($normalizedRow['bulan_pembelian']);
                    if ($bulanAngka) {
                        $tanggal_pembelian = Carbon::create($normalizedRow['tahun_pembelian'], $bulanAngka, $normalizedRow['tanggal_pembelian']);
                    }
                }
            } catch (\Exception $e) {
                $tanggal_pembelian = null;
            }

            $assetData = [
                'nama_barang' => $normalizedRow['nama_barang'],
                'category_id' => $category->id,
                'sub_category_id' => optional($subCategory)->id,
                'company_id' => optional($company)->id,
                'asset_user_id' => optional($assetUser)->id,
                'merk' => $normalizedRow['merk'] ?? null,
                'tipe' => $normalizedRow['tipe'] ?? null,
                'serial_number' => $normalizedRow['serial_number'] ?? null,
                'kondisi' => $normalizedRow['kondisi'] ?? 'Baik',
                'lokasi' => $normalizedRow['lokasi'] ?? null,
                'jumlah' => $normalizedRow['jumlah'] ?? 1,
                'satuan' => $normalizedRow['satuan'] ?? 'Unit',
                'tanggal_pembelian' => $tanggal_pembelian,
                'harga_total' => $normalizedRow['harga_total_rp'] ?? null,
                'po_number' => $normalizedRow['nomor_po'] ?? null,
                'nomor' => $normalizedRow['nomor_bast'] ?? null,
                'code_aktiva' => $normalizedRow['kode_aktiva'] ?? null,
                'sumber_dana' => $normalizedRow['sumber_dana'] ?? null,
                'include_items' => $normalizedRow['item_termasuk'] ?? null,
                'peruntukan' => $normalizedRow['peruntukan'] ?? null,
                'keterangan' => $normalizedRow['keterangan'] ?? null,
                'specifications' => $this->getSpecifications($normalizedRow),
            ];

            $asset = !empty($assetData['serial_number']) 
                ? Asset::where('serial_number', $assetData['serial_number'])->first() 
                : null;

            $oldUserId = $asset ? $asset->asset_user_id : null;

            if ($asset) {
                $asset->update($assetData);
            } else {
                $assetData['code_asset'] = 'TEMP-' . uniqid();
                $asset = Asset::create($assetData);

                $newCode = $this->generateAssetCode($normalizedRow, $category, $subCategory, $company, $asset->id);
                $asset->code_asset = $newCode;
                $asset->save();
            }

            $newUserId = $asset->asset_user_id;

            if ($oldUserId != $newUserId) {
                if ($oldUserId) {
                    $asset->history()->where('asset_user_id', $oldUserId)->whereNull('tanggal_selesai')->update(['tanggal_selesai' => now()]);
                }
                if ($newUserId) {
                    $asset->history()->create(['asset_user_id' => $newUserId, 'tanggal_mulai' => now()]);
                }
            }
        }
    }

    private function generateAssetCode(array $row, Category $category, ?SubCategory $subCategory, ?Company $company, int $assetId): string
    {
        $getFourDigits = fn($s) => strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', (string)$s), 0, 4));
        $getThreeDigits = fn($s) => strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', (string)$s), 0, 3));
        
        $companyCode = $getThreeDigits(optional($company)->code);
        $paddedId = str_pad($assetId, 3, '0', STR_PAD_LEFT);

        if (empty($row['merk']) && empty($row['tipe'])) {
            $kategoriCode = $getFourDigits($category->code);
            $subKategoriCode = $getFourDigits(optional($subCategory)->name);
            return "{$kategoriCode}/{$subKategoriCode}/{$companyCode}/{$paddedId}";
        }
      
        if ($category->code === 'ELEC') {
            $jenisBarangCode = $getFourDigits(optional($subCategory)->name);
            $merkCode = $getFourDigits($row['merk'] ?? '');
            return "{$jenisBarangCode}/{$merkCode}/{$companyCode}/{$paddedId}";
        } elseif ($category->code === 'VEHI') {
            $jenisBarangCode = $getFourDigits(optional($subCategory)->name);
            $namaBarangCode = $getFourDigits($row['nama_barang'] ?? '');
            return "{$jenisBarangCode}/{$namaBarangCode}/{$companyCode}/{$paddedId}";
        }
       
        $kategoriCode = $getFourDigits($category->code);
        $namaBarangCode = $getFourDigits($row['nama_barang'] ?? '');
        return "{$namaBarangCode}/{$kategoriCode}/{$companyCode}/{$paddedId}";
    }
    
    private function generateCompanyCode(string $companyName): string
    {
        $nameWithoutPt = $this->cleanCompanyName($companyName);
        
        $words = explode(' ', $nameWithoutPt);
        $code = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $code .= strtoupper($word[0]);
            }
        }
        
        return $code;
    }

    
    private function cleanCompanyName(string $companyName): string
    {
        return trim(preg_replace('/^(pt\.?|cv\.?)\s*/i', '', $companyName));
    }

    public function rules(): array
    {
        return [
            '*.nama_barang' => 'required|string',
            '*.kategori' => 'required|string',
        ];
    }
    
    private function normalizeRowKeys(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $cleanKey = preg_replace('/ \(.+\)/', '', $key);
            $newKey = Str::snake(strtolower($cleanKey));
            $normalized[$newKey] = $value;
        }
        return $normalized;
    }

    private function getStandardColumns(): array
    {
        return [
            'nama_barang', 'kategori', 'sub_kategori', 'perusahaan_pemilik', 'merk', 'tipe', 'serial_number', 'pengguna_aset',
            'jabatan_pengguna', 'departemen_pengguna', 'perusahaan_pengguna', 'kondisi', 'lokasi', 'jumlah',
            'satuan', 'harga_total_rp', 'nomor_po', 'nomor_bast', 'kode_aktiva', 'sumber_dana',
            'item_termasuk', 'peruntukan', 'keterangan', 'kode_aset', 'riwayat_pengguna',
            'hari_pembelian', 'tanggal_pembelian', 'bulan_pembelian', 'tahun_pembelian'
        ];
    }

    private function getSpecifications(array $normalizedRow): array
    {
        $specifications = [];
        $standardColumns = $this->getStandardColumns();
        foreach ($normalizedRow as $key => $value) {
            if (!in_array($key, $standardColumns) && !empty($value)) {
                $specifications[Str::title(str_replace('_', ' ', $key))] = $value;
            }
        }
        return $specifications;
    }

    private function getMonthNumber($monthName): ?int
    {
        if (is_numeric($monthName)) return (int)$monthName;
        $months = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6,
            'juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];
        return $months[strtolower(trim($monthName))] ?? null;
    }
}
