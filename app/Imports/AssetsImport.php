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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


    class AssetsImport implements ToCollection, WithHeadingRow, WithValidation
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            $normalizedRow = $this->normalizeRowKeys($row->toArray());

            if (empty($normalizedRow['nama_barang']) || empty($normalizedRow['kategori'])) {
                continue;
            }

            $category = Category::firstOrCreate(
                ['name' => $normalizedRow['kategori']],
                ['code' => substr(strtoupper(Str::slug($normalizedRow['kategori'])), 0, 4)]
            );
            
            $subCategory = ($category && !empty($normalizedRow['sub_kategori'])) 
                ? SubCategory::firstOrCreate(
                    ['name' => $normalizedRow['sub_kategori'], 'category_id' => $category->id]
                ) 
                : null;
            
            $company = null;
            if (!empty($normalizedRow['perusahaan_pemilik'])) {
                $companyName = $this->cleanCompanyName($normalizedRow['perusahaan_pemilik']);
                $company = Company::firstOrCreate(
                    ['name' => $companyName],
                    ['code' => $this->generateUniqueCompanyCode($companyName)]
                );
            }
            
            $assetUser = null;
            if (!empty($normalizedRow['pengguna_aset'])) {
                $userCompany = null;
                if (!empty($normalizedRow['perusahaan_pengguna'])) {
                    $userCompanyName = $this->cleanCompanyName($normalizedRow['perusahaan_pengguna']);
                    $userCompany = Company::firstOrCreate(
                        ['name' => $userCompanyName],
                        ['code' => $this->generateUniqueCompanyCode($userCompanyName)]
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
            $newAssetUserId = optional($assetUser)->id;

            $tanggal_pembelian = null;
            try {
                if (!empty($normalizedRow['tahun_pembelian']) && !empty($normalizedRow['bulan_pembelian']) && !empty($normalizedRow['tanggal_pembelian'])) {
                    $bulanAngka = $this->getMonthNumber($normalizedRow['bulan_pembelian']);
                    if ($bulanAngka) {
                        $tanggal_pembelian = Carbon::create((int)$normalizedRow['tahun_pembelian'], $bulanAngka, (int)$normalizedRow['tanggal_pembelian']);
                    }
                }
            } catch (\Exception $e) {
                $tanggal_pembelian = null;
            }

            $hargaTotalRaw = $normalizedRow['harga_total_rp'] ?? null;
            $hargaTotalClean = $hargaTotalRaw ? preg_replace('/[^0-9]/', '', $hargaTotalRaw) : null;

            $assetData = [
                'nama_barang' => $normalizedRow['nama_barang'],
                'category_id' => $category->id,
                'sub_category_id' => optional($subCategory)->id,
                'company_id' => optional($company)->id,
                'asset_user_id' => $newAssetUserId,
                'merk' => $normalizedRow['merk'] ?? null,
                'tipe' => $normalizedRow['tipe'] ?? null,
                'serial_number' => $normalizedRow['serial_number'] ?? null,
                'kondisi' => $normalizedRow['kondisi'] ?? 'Baik',
                'lokasi' => $normalizedRow['lokasi'] ?? null,
                'jumlah' => $normalizedRow['jumlah'] ?? 1,
                'satuan' => $normalizedRow['satuan'] ?? 'Unit',
                'tanggal_pembelian' => $tanggal_pembelian,
                'harga_total' => $hargaTotalClean,
                'po_number' => $normalizedRow['nomor_po'] ?? null,
                'nomor' => $normalizedRow['nomor_bast'] ?? null,
                'code_aktiva' => $normalizedRow['kode_aktiva'] ?? null,
                'sumber_dana' => $normalizedRow['sumber_dana'] ?? null,
                'include_items' => $normalizedRow['item_termasuk'] ?? null,
                'peruntukan' => $normalizedRow['peruntukan'] ?? null,
                'keterangan' => $normalizedRow['keterangan'] ?? null,
                'specifications' => $this->getSpecifications($normalizedRow),
            ];

            $asset = null;
            if (!empty($assetData['nomor']) && trim($assetData['nomor']) !== '-') {
                $asset = Asset::where('nomor', $assetData['nomor'])->first();
            }

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

            $currentUserId = $asset->fresh()->asset_user_id;

            if ($oldUserId != $currentUserId) {
                if ($oldUserId) {
                    $asset->history()->where('asset_user_id', $oldUserId)->whereNull('tanggal_selesai')->update(['tanggal_selesai' => now()]);
                }
                if ($currentUserId) {
                    $currentUser = AssetUser::find($currentUserId);
                    $asset->history()->create([
                        'asset_user_id' => $currentUserId,
                        'historical_user_name' => $currentUser->nama,
                        'tanggal_mulai' => now(),
                    ]);
                }
            } elseif ($currentUserId && $asset->history()->count() == 0) {
                 $currentUser = AssetUser::find($currentUserId);
                 $asset->history()->create([
                    'asset_user_id' => $currentUserId,
                    'historical_user_name' => $currentUser->nama,
                    'tanggal_mulai' => now(),
                ]);
            }
        }
    }
    
    // --- METHOD INI DIMODIFIKASI ---
    private function generateAssetCode(array $row, Category $category, ?SubCategory $subCategory, ?Company $company, int $assetId): string
    {
        $companyCode = optional($company)->code ?? 'N/A';
        $categoryCode = $category->code ?? 'N/A';

        $itemName = preg_replace('/[^a-zA-Z0-9]/', '', (string) $row['nama_barang']);
        $itemNamePart = strtoupper(substr($itemName, 0, 4));

        $paddedId = str_pad($assetId, 5, '0', STR_PAD_LEFT);

        return "{$companyCode}/{$categoryCode}/{$itemNamePart}/{$paddedId}";
    }
    // --- AKHIR MODIFIKASI ---
    
    private function cleanCompanyName(string $companyName): string
    {
        return trim(preg_replace('/^(pt\.?|cv\.?)\s*/i', '', $companyName));
    }

    private function generateUniqueCompanyCode(string $companyName): string
    {
        $cleanName = $this->cleanCompanyName($companyName);
        $words = array_values(array_filter(explode(' ', $cleanName)));
        
        $code = '';
        foreach ($words as $word) {
            $code .= strtoupper(substr($word, 0, 1));
        }
        $code = substr($code, 0, 10);

        if (Company::where('code', $code)->where('name', '!=', $cleanName)->exists()) {
            if (count($words) > 1 && strlen($words[1]) > 1) {
                $altCode = strtoupper(substr($words[0], 0, 1) . substr($words[1], 1, 1));
                $altCode = substr($altCode, 0, 10);
                if (!Company::where('code', $altCode)->exists()) {
                    return $altCode;
                }
            }
            $i = 2;
            $baseCode = $code;
            while (Company::where('code', $code)->exists()) {
                $code = substr($baseCode, 0, 9) . $i;
                $i++;
            }
        }
        return $code;
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
            $newKey = str_replace(['harga_total_rp', ' spesifikasi_deskripsi_lainnya'], ['harga_total_rp', 'deskripsi'], Str::snake(strtolower($key)));
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