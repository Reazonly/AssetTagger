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
        // Kita akan mencari berdasarkan nama, jadi kita siapkan datanya
        $this->companies = Company::all()->keyBy('name');
        $this->categories = Category::all()->keyBy('name');
        $this->subCategories = SubCategory::all()->keyBy('name');
    }

    public function collection(Collection $rows)
    {
        // Ambil header dari baris pertama untuk mendeteksi format file
        $headers = collect($rows->first())->keys()->map(fn($item) => Str::snake($item));

        // Tentukan peta kolom berdasarkan header yang ditemukan
        $map = $this->getColumnMapping($headers);

        // Jika peta tidak valid, hentikan proses
        if (!$map) {
            // Di sini Anda bisa menambahkan log atau notifikasi error jika diperlukan
            return; 
        }

        foreach ($rows as $row) 
        {
            // Ubah semua key di baris menjadi snake_case agar konsisten
            $row = collect($row)->keyBy(fn($value, $key) => Str::snake($key));

            if (empty($row[$map['nama_barang']])) {
                continue;
            }

            // Proses data menggunakan peta kolom
            $category = $this->categories[trim($row[$map['kategori']] ?? '')] ?? null;
            $subCategory = $this->subCategories[trim($row[$map['sub_kategori']] ?? '')] ?? null;
            $company = $this->companies[trim($row[$map['perusahaan']] ?? '')] ?? null;

            $user = null;
            if (!empty($row[$map['pengguna']])) {
                $namaPengguna = trim($row[$map['pengguna']]);
                $user = User::updateOrCreate( // Gunakan updateOrCreate agar lebih fleksibel
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
                'nama_barang'       => trim($row[$map['nama_barang']]),
                'category_id'       => optional($category)->id,
                'sub_category_id'   => optional($subCategory)->id,
                'company_id'        => optional($company)->id,
                'serial_number'     => trim($row[$map['serial_number']] ?? null),
                'kondisi'           => trim($row[$map['kondisi']] ?? 'BAIK'),
                // ... dan seterusnya untuk semua field menggunakan $map
            ];
            
            $asset = Asset::updateOrCreate(
                ['serial_number' => $assetData['serial_number']], // Cari berdasarkan Serial Number
                $assetData // Data untuk diupdate atau dibuat
            );
            
            if ($user) {
                $this->updateUserHistory($asset, $user->id);
            }
        }
    }

    /**
     * Fungsi baru untuk menentukan dan menyediakan peta kolom.
     * Di sinilah "kecerdasan" pemetaan terjadi.
     */
    private function getColumnMapping(Collection $headers): ?array
    {
        // Peta untuk format file dari hasil EKSPOR INTERNAL
        $internalExportMap = [
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

        // Peta untuk format file "CONTOH REPORT" (Asumsi nama kolom)
        // PENTING: Sesuaikan 'nama_item', 'jenis', dll dengan nama kolom di file Anda
        $contohReportMap = [
            'nama_barang' => 'nama_item', // Misal, di file ini headernya "Nama Item"
            'kategori' => 'kategori_barang', // Misal, di file ini headernya "Kategori Barang"
            'sub_kategori' => 'jenis',
            'perusahaan' => 'milik_perusahaan',
            'pengguna' => 'nama_2',
            'jabatan' => 'jabatan_2',
            'departemen' => 'departemen_2',
            'serial_number' => 'serial_number',
            'kondisi' => 'kondisi',
        ];

        // Logika Deteksi: Cek apakah header unik dari salah satu format ada
        if ($headers->contains('pengguna_saat_ini')) {
            return $internalExportMap; // Gunakan peta internal
        }
        
        if ($headers->contains('nama_item')) {
            return $contohReportMap; // Gunakan peta contoh report
        }

        // Jika tidak ada format yang cocok
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