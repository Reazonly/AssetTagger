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

            // 1. Cari atau buat data master (Logika Company dikembalikan)
            $category = Category::firstOrCreate(
                ['name' => $normalizedRow['kategori']],
                ['code' => substr(strtoupper(Str::slug($normalizedRow['kategori'])), 0, 4), 'slug' => Str::slug($normalizedRow['kategori'])]
            );

            $subCategory = ($category && !empty($normalizedRow['sub_kategori'])) 
                ? SubCategory::firstOrCreate(['name' => $normalizedRow['sub_kategori'], 'category_id' => $category->id]) 
                : null;
            
            // Logika untuk mencari perusahaan pemilik berdasarkan kode
            $company = !empty($normalizedRow['perusahaan_pemilik_kode']) 
                ? Company::where('code', $normalizedRow['perusahaan_pemilik_kode'])->first() 
                : null;
            
            $assetUser = null;
            if (!empty($normalizedRow['pengguna_aset'])) {
                // Logika untuk mencari perusahaan pengguna berdasarkan kode
                $userCompany = !empty($normalizedRow['perusahaan_pengguna_kode']) ? Company::where('code', $normalizedRow['perusahaan_pengguna_kode'])->first() : null;
                $assetUser = AssetUser::firstOrCreate(
                    ['nama' => $normalizedRow['pengguna_aset']],
                    [
                        'jabatan' => $normalizedRow['jabatan_pengguna'] ?? null, 
                        'departemen' => $normalizedRow['departemen_pengguna'] ?? null,
                        'company_id' => optional($userCompany)->id // Menetapkan company_id untuk pengguna
                    ]
                );
            }

            // 2. Siapkan data aset
            $specifications = [];
            foreach ($normalizedRow as $key => $value) {
                if (!in_array($key, $this->defaultHeadings()) && !empty($value)) {
                    $specifications[$key] = $value;
                }
            }

            $tanggal_pembelian = null;
            if (!empty($normalizedRow['tanggal_pembelian'])) {
                try {
                    if (is_numeric($normalizedRow['tanggal_pembelian'])) {
                        $tanggal_pembelian = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($normalizedRow['tanggal_pembelian']);
                    } else {
                        $tanggal_pembelian = Carbon::parse($normalizedRow['tanggal_pembelian']);
                    }
                } catch (\Exception $e) {
                    $tanggal_pembelian = null;
                }
            }

            $assetData = [
                'nama_barang' => $normalizedRow['nama_barang'],
                'category_id' => optional($category)->id,
                'sub_category_id' => optional($subCategory)->id,
                'company_id' => optional($company)->id, // Menetapkan company_id untuk aset
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
                'specifications' => $specifications,
            ];

            // 3. Cari aset untuk diupdate atau buat baru
            $asset = null;
            if (!empty($normalizedRow['kode_aset'])) {
                $asset = Asset::withTrashed()->where('code_asset', $normalizedRow['kode_aset'])->first();
            } elseif (!empty($normalizedRow['serial_number'])) {
                $asset = Asset::withTrashed()->where('serial_number', $normalizedRow['serial_number'])->first();
            }

            if ($asset) {
                if ($asset->trashed()) { $asset->restore(); }
                $asset->update($assetData);
            } else {
                $assetData['code_asset'] = $normalizedRow['kode_aset'] ?? ('TEMP-' . uniqid());
                $asset = Asset::create($assetData);
            }

            // 4. Update riwayat pengguna
            $asset->history()->whereNull('tanggal_selesai')->update(['tanggal_selesai' => now()]);
            if ($asset->asset_user_id) {
                $asset->history()->create([
                    'asset_user_id' => $asset->asset_user_id,
                    'tanggal_mulai' => now(),
                ]);
            }
        }
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
            $newKey = Str::snake(strtolower(preg_replace('/ \(.+\)/', '', $key)));
            $normalized[$newKey] = $value;
        }
        return $normalized;
    }

    private function defaultHeadings(): array
    {
        // Mengembalikan kolom kode perusahaan
        return [
            'kode_aset', 'nama_barang', 'kategori', 'sub_kategori', 'perusahaan_pemilik_kode',
            'merk', 'tipe', 'serial_number', 'pengguna_aset', 'jabatan_pengguna',
            'departemen_pengguna', 'perusahaan_pengguna_kode', 'kondisi', 'lokasi', 'jumlah',
            'satuan', 'tanggal_pembelian', 'harga_total_rp', 'nomor_po', 'nomor_bast',
            'kode_aktiva', 'sumber_dana', 'item_termasuk', 'peruntukan', 'keterangan',
            'riwayat_pengguna',
        ];
    }
}