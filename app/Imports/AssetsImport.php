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
        // Ambil header dari baris pertama untuk mendeteksi format file
        $headers = collect($rows->first())->keys()->map(fn($item) => Str::snake($item));
        $map = $this->getColumnMapping($headers);

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
            
            // PERUBAHAN: Menambahkan 'code_asset' ke dalam data yang disimpan
            $assetData = [
                'code_asset'        => trim($row[$map['code_asset']] ?? null),
                'nama_barang'       => trim($row[$map['nama_barang']]),
                'category_id'       => optional($category)->id,
                'sub_category_id'   => optional($subCategory)->id,
                'company_id'        => optional($company)->id,
                'serial_number'     => trim($row[$map['serial_number']] ?? null),
                'kondisi'           => trim($row[$map['kondisi']] ?? 'BAIK'),
            ];
            
            // Logika baru: Gunakan updateOrCreate tapi pastikan code_asset ada saat membuat
            $asset = Asset::updateOrCreate(
                ['serial_number' => $assetData['serial_number']],
                $assetData
            );
            
            if ($user) {
                $this->updateUserHistory($asset, $user->id);
            }
        }
    }

    private function getColumnMapping(Collection $headers): ?array
    {
        // Peta untuk format file dari hasil EKSPOR INTERNAL
        $internalExportMap = [
            'code_asset' => 'kode_aset', // <-- DITAMBAHKAN
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

        // Peta untuk format file "CONTOH REPORT" 
        $contohReportMap = [
            'code_asset' => 'code_asset', // <-- DITAMBAHKAN (sesuaikan jika perlu)
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

        // Logika Deteksi
        if ($headers->contains('kode_aset')) { // Mengecek header dari file ekspor
            return $internalExportMap;
        }
        
        if ($headers->contains('nama_item')) { // Mengecek header dari file contoh report
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