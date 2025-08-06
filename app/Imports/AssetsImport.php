<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\User;
use App\Models\Category;
use App\Models\Company;
use App\Models\SubCategory; // <-- Ditambahkan
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
    private $subCategories; // <-- Ditambahkan

    public function __construct()
    {
        $this->companies = Company::all()->keyBy('name'); // Cari berdasarkan nama, bukan kode
        $this->categories = Category::all()->keyBy('name');
        $this->subCategories = SubCategory::all()->keyBy('name'); // Pre-load sub-kategori
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            // PERUBAHAN: Mencari berdasarkan 'nama_barang' dari file ekspor
            if (empty($row['nama_barang'])) {
                continue;
            }

            // 1. Menentukan Kategori berdasarkan nama dari file ekspor
            $category = $this->categories[trim($row['kategori'] ?? '')] ?? null;
            
            // PERUBAHAN: Menentukan Sub Kategori berdasarkan nama
            $subCategory = $this->subCategories[trim($row['sub_kategori'] ?? '')] ?? null;

            // 2. Menentukan Perusahaan berdasarkan nama dari file ekspor
            $company = $this->companies[trim($row['perusahaan'] ?? '')] ?? null;

            // 3. Cari atau buat User berdasarkan nama dari file ekspor
            $user = null;
            // PERUBAHAN: Mencari berdasarkan 'pengguna_saat_ini'
            if (!empty($row['pengguna_saat_ini'])) {
                $namaPengguna = trim($row['pengguna_saat_ini']);
                $user = User::firstOrCreate(
                    ['nama_pengguna' => $namaPengguna],
                    [
                        'email' => Str::slug($namaPengguna) . '_' . time() . '@jhonlin.local',
                        'password' => Hash::make(Str::random(12)),
                        'jabatan' => trim($row['jabatan_pengguna'] ?? null), 
                        'departemen' => trim($row['departemen_pengguna'] ?? null)
                    ]
                );
            }
            
            // 4. Kumpulkan data spesifikasi dinamis
            $specifications = $this->collectSpecifications($row);

            // 5. Siapkan data utama untuk aset
            $assetData = [
                'nama_barang'       => trim($row['nama_barang']),
                'category_id'       => optional($category)->id,
                'sub_category_id'   => optional($subCategory)->id,
                'company_id'        => optional($company)->id,
                'merk'              => trim($row['merk'] ?? null),
                'tipe'              => trim($row['tipe'] ?? null),
                'serial_number'     => trim($row['serial_number'] ?? null),
                'kondisi'           => trim($row['kondisi'] ?? 'BAIK'),
                'lokasi'            => trim($row['lokasi_fisik'] ?? null),
                'jumlah'            => is_numeric($row['jumlah'] ?? null) ? $row['jumlah'] : 1,
                'satuan'            => trim($row['satuan'] ?? 'Unit'),
                'user_id'           => optional($user)->id,
                'specifications'    => $specifications,
                'tanggal_pembelian' => isset($row['tanggal_pembelian']) ? Carbon::createFromFormat('d-m-Y', $row['tanggal_pembelian'])->toDateString() : null,
                'thn_pembelian'     => trim($row['tahun_pembelian'] ?? null),
                'harga_total'       => is_numeric($row['harga_total_rp'] ?? null) ? $row['harga_total_rp'] : null,
                'po_number'         => trim($row['nomor_po'] ?? null),
                'nomor'             => trim($row['nomor_bast'] ?? null),
                'code_aktiva'       => trim($row['kode_aktiva'] ?? null),
                'sumber_dana'       => trim($row['sumber_dana'] ?? null),
                'include_items'     => trim($row['item_termasuk'] ?? null),
                'peruntukan'        => trim($row['peruntukan'] ?? null),
                'keterangan'        => trim($row['keterangan'] ?? null),
            ];
            
            // 6. Gunakan 'serial_number' sebagai kunci unik untuk update/create
            $asset = null;
            $serialNumber = trim($row['serial_number'] ?? null);
            if (!empty($serialNumber)) {
                $asset = Asset::withTrashed()->where('serial_number', $serialNumber)->first();
            }

            if ($asset) {
                $asset->update($assetData);
                if ($asset->trashed()) {
                    $asset->restore();
                }
            } else {
                $assetData['code_asset'] = trim($row['kode_aset']);
                $asset = Asset::create($assetData);
            }
            
            if ($user) {
                $this->updateUserHistory($asset, $user->id);
            }
        }
    }

    private function collectSpecifications(Collection $row): array
    {
        $specs = [];
        $specMap = [
            'processor' => 'processor', 'ram' => 'ram', 'storage' => 'storage', 
            'graphics' => 'graphics', 'layar' => 'layar',
            'nomor_polisi' => 'nomor_polisi', 'nomor_rangka' => 'nomor_rangka', 'nomor_mesin' => 'nomor_mesin',
            'spesifikasideskripsi_lainnya' => 'deskripsi', // Kunci dari header digabung dan lowercase
        ];

        foreach ($specMap as $headerKey => $specKey) {
            if (isset($row[$headerKey]) && !empty($row[$headerKey])) {
                $specs[$specKey] = trim($row[$headerKey]);
            }
        }
        return $specs;
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
    
    // Fungsi parseDate tidak lagi dibutuhkan karena Carbon bisa membaca format 'd-m-Y'
    
    public function chunkSize(): int
    {
        return 100;
    }

    // PERUBAHAN: Menambahkan formatter untuk nama header
    public function headingRowFormatter($row) {
        return collect($row)->map(function ($value) {
            // Mengubah "Nama Barang" menjadi "nama_barang"
            return Str::snake(strtolower($value));
        })->toArray();
    }
}