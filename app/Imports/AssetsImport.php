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
            // 1. Cari atau buat data master berdasarkan nama
            $category = Category::firstOrCreate(
                ['name' => $row['kategori']],
                ['code' => Str::slug($row['kategori'])]
            );

            $subCategory = null;
            if (!empty($row['sub_kategori'])) {
                $subCategory = SubCategory::firstOrCreate(
                    ['name' => $row['sub_kategori'], 'category_id' => $category->id]
                );
            }
            
            $company = null;
            if (!empty($row['perusahaan_pemilik'])) {
                $company = Company::firstOrCreate(
                    ['name' => $row['perusahaan_pemilik']],
                    ['code' => Str::slug($row['perusahaan_pemilik'])]
                );
            }

            $assetUser = null;
            if (!empty($row['pengguna_aset'])) {
                 $userCompany = null;
                 if (!empty($row['perusahaan_pengguna'])) {
                    $userCompany = Company::firstOrCreate(
                        ['name' => $row['perusahaan_pengguna']],
                        ['code' => Str::slug($row['perusahaan_pengguna'])]
                    );
                 }
                $assetUser = AssetUser::firstOrCreate(
                    ['nama' => $row['pengguna_aset']],
                    [
                        'jabatan' => $row['jabatan_pengguna'] ?? null,
                        'departemen' => $row['departemen_pengguna'] ?? null,
                        'company_id' => optional($userCompany)->id
                    ]
                );
            }

            // 2. Kumpulkan semua kolom spesifikasi kustom
            $specifications = [];
            foreach ($row as $key => $value) {
                if (!in_array($key, $this->defaultHeadings()) && !empty($value)) {
                    $specifications[$key] = $value;
                }
            }

            // 3. Perbaiki penanganan tanggal
            $tanggal_pembelian = null;
            if (!empty($row['tanggal_pembelian'])) {
                if (is_numeric($row['tanggal_pembelian'])) {
                    $tanggal_pembelian = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tanggal_pembelian']);
                } else {
                    try {
                        $tanggal_pembelian = Carbon::parse($row['tanggal_pembelian']);
                    } catch (\Exception $e) {
                        $tanggal_pembelian = null;
                    }
                }
            }

            // 4. Buat aset baru
            $asset = Asset::create([
                'code_asset' => $row['kode_aset'] ?? ('TEMP-' . time() . rand(100,999)),
                'nama_barang' => $row['nama_barang'],
                'category_id' => $category->id,
                'sub_category_id' => optional($subCategory)->id,
                'company_id' => optional($company)->id,
                'merk' => $row['merk'] ?? null,
                'tipe' => $row['tipe'] ?? null,
                'serial_number' => $row['serial_number'] ?? null,
                'asset_user_id' => optional($assetUser)->id,
                'kondisi' => $row['kondisi'] ?? 'Baik',
                'lokasi' => $row['lokasi'] ?? null,
                'jumlah' => $row['jumlah'] ?? 1,
                'satuan' => $row['satuan'] ?? 'Unit',
                'tanggal_pembelian' => $tanggal_pembelian,
                'harga_total' => $row['harga_total_rp'] ?? null,
                'po_number' => $row['nomor_po'] ?? null,
                'nomor' => $row['nomor_bast'] ?? null,
                'code_aktiva' => $row['kode_aktiva'] ?? null,
                'sumber_dana' => $row['sumber_dana'] ?? null,
                'include_items' => $row['item_termasuk'] ?? null,
                'peruntukan' => $row['peruntukan'] ?? null,
                'keterangan' => $row['keterangan'] ?? null,
                'specifications' => $specifications,
            ]);

            // --- PERBAIKAN DI SINI ---
            // 5. Jika ada pengguna aset, buat entri riwayat pertamanya.
            if ($asset && $asset->asset_user_id) {
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

    private function defaultHeadings(): array
    {
        return [
            'kode_aset', 'nama_barang', 'kategori', 'sub_kategori', 'perusahaan_pemilik',
            'merk', 'tipe', 'serial_number', 'pengguna_aset', 'jabatan_pengguna',
            'departemen_pengguna', 'perusahaan_pengguna', 'kondisi', 'lokasi', 'jumlah',
            'satuan', 'tanggal_pembelian', 'harga_total_rp', 'nomor_po', 'nomor_bast',
            'kode_aktiva', 'sumber_dana', 'item_termasuk', 'peruntukan', 'keterangan',
            'riwayat_pengguna',
        ];
    }
}
