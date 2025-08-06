<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\User;
use App\Models\Category;
use App\Models\Company;
use App\Models\SubCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AssetsImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private $companies;
    private $categories;
    private $subCategories;

    public function __construct()
    {
        $this->companies = Company::all()->keyBy('name');
        $this->categories = Category::all()->keyBy('name');
        $this->subCategories = SubCategory::all()->keyBy('name');
    }

    public function collection(Collection $rows)
    {
        $map = $this->getColumnMapping(collect($rows->first())->keys());

        if (!$map) {
            return; 
        }

        foreach ($rows as $row) 
        {
            $row = collect($row)->keyBy(fn($value, $key) => Str::snake($key));

            if (empty($row[$map['nama_barang']])) {
                continue;
            }

            $category = $this->categories[trim($row[$map['kategori']] ?? '')] ?? null;
            $subCategory = $this->subCategories[trim($row[$map['sub_kategori']] ?? '')] ?? null;
            $company = $this->companies[trim($row[$map['perusahaan']] ?? '')] ?? null;

            $user = null;
            if (!empty($row[$map['pengguna']])) {
                $namaPengguna = trim($row[$map['pengguna']]);
                $user = User::updateOrCreate(
                    ['nama_pengguna' => $namaPengguna],
                    [
                        'email' => Str::slug($namaPengguna) . '_' . time() . '@jhonlin.local',
                        'password' => Hash::make(Str::random(12)),
                        'jabatan' => trim($row[$map['jabatan']] ?? null), 
                        'departemen' => trim($row[$map['departemen']] ?? null)
                    ]
                );
            }
            
            $assetData = [
                'code_asset'        => trim($row[$map['code_asset']] ?? null),
                'nama_barang'       => trim($row[$map['nama_barang']]),
                'category_id'       => optional($category)->id,
                'sub_category_id'   => optional($subCategory)->id,
                'company_id'        => optional($company)->id,
                'serial_number'     => trim($row[$map['serial_number']] ?? null),
                'kondisi'           => trim($row[$map['kondisi']] ?? 'BAIK'),
            ];
            
            // --- PERBAIKAN TOTAL PADA LOGIKA PENYIMPANAN ---
            $asset = null;
            // 1. Coba cari berdasarkan Serial Number jika ada
            if (!empty($assetData['serial_number'])) {
                $asset = Asset::where('serial_number', $assetData['serial_number'])->first();
            }
            // 2. Jika tidak ketemu, coba cari berdasarkan Kode Aset
            if (!$asset && !empty($assetData['code_asset'])) {
                $asset = Asset::where('code_asset', $assetData['code_asset'])->first();
            }

            // 3. Jika aset ditemukan (dari salah satu cara di atas), perbarui datanya
            if ($asset) {
                $asset->update($assetData);
            } 
            // 4. Jika sama sekali tidak ditemukan, buat aset baru
            else {
                // Pastikan code_asset tidak kosong saat membuat baru
                if (!empty($assetData['code_asset'])) {
                    Asset::create($assetData);
                }
            }
            // --- AKHIR PERBAIKAN ---
            
            if ($user && $asset) {
                $this->updateUserHistory($asset, $user->id);
            }
        }
    }

    private function getColumnMapping(Collection $keys): ?array
    {
        $headers = $keys->map(fn($item) => Str::snake($item));

        $internalExportMap = [
            'code_asset' => 'kode_aset',
            'nama_barang' => 'nama_barang',
            'kategori' => 'kategori',
            'sub_kategori' => 'sub_kategori',
            'perusahaan' => 'perusahaan',
            'pengguna' => 'pengguna_saat_ini',
            'jabatan' => 'jabatan_pengguna',
            'departemen' => 'departemen_pengguna',
            'serial_number' => 'serial_number',
            'kondisi' => 'kondisi',
        ];

        $contohReportMap = [
            'code_asset' => 'code_asset',
            'nama_barang' => 'nama_item',
            'kategori' => 'kategori_barang',
            'sub_kategori' => 'jenis',
            'perusahaan' => 'milik_perusahaan',
            'pengguna' => 'nama_2',
            'jabatan' => 'jabatan_2',
            'departemen' => 'departemen_2',
            'serial_number' => 'serial_number',
            'kondisi' => 'kondisi',
        ];

        if ($headers->contains('kode_aset')) {
            return $internalExportMap;
        }
        if ($headers->contains('nama_item')) {
            return $contohReportMap;
        }
        return null;
    }

    private function updateUserHistory(Asset $asset, ?int $newUserId): void
    {
        if(!$newUserId) return;
        $latestHistory = $asset->history()->latest()->first();
        if (!$latestHistory || $latestHistory->user_id != $newUserId) {
            $asset->history()->whereNull('tanggal_selesai')->update(['tanggal_selesai' => now()]);
            $asset->history()->create(['user_id' => $newUserId, 'tanggal_mulai' => now()]);
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }
}