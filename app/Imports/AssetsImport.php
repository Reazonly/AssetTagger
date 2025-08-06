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
            dd($row);
        
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
            
            // PERUBAHAN: Memanggil spesifikasi menggunakan map
            $specifications = $this->collectSpecifications($row, $map);
            $hargaTotal = trim($row[$map['harga_total']] ?? '');
            $thnPembelian = trim($row[$map['tahun_pembelian']] ?? '');

            // PERUBAHAN: Melengkapi semua field menggunakan map
            $assetData = [
                'code_asset'        => trim($row[$map['code_asset']] ?? null),
                'nama_barang'       => trim($row[$map['nama_barang']]),
                'category_id'       => optional($category)->id,
                'sub_category_id'   => optional($subCategory)->id,
                'company_id'        => optional($company)->id,
                'merk'              => trim($row[$map['merk']] ?? null),
                'tipe'              => trim($row[$map['tipe']] ?? null),
                'serial_number'     => trim($row[$map['serial_number']] ?? null),
                'kondisi'           => trim($row[$map['kondisi']] ?? 'BAIK'),
                'lokasi'            => trim($row[$map['lokasi']] ?? null),
                'jumlah'            => is_numeric($row[$map['jumlah']] ?? null) ? $row[$map['jumlah']] : 1,
                'satuan'            => trim($row[$map['satuan']] ?? 'Unit'),
                'user_id'           => optional($user)->id,
                'specifications'    => $specifications,
                'tanggal_pembelian' => !empty($row[$map['tanggal_pembelian']]) ? Carbon::parse($row[$map['tanggal_pembelian']])->toDateString() : null,
                'thn_pembelian'     => !empty($thnPembelian) && is_numeric($thnPembelian) ? $thnPembelian : null,
                'harga_total'       => !empty($hargaTotal) && is_numeric($hargaTotal) ? $hargaTotal : null,
                'po_number'         => trim($row[$map['po_number']] ?? null),
                'nomor'             => trim($row[$map['nomor_bast']] ?? null),
                'code_aktiva'       => trim($row[$map['code_aktiva']] ?? null),
                'sumber_dana'       => trim($row[$map['sumber_dana']] ?? null),
                'include_items'     => trim($row[$map['item_termasuk']] ?? null),
                'peruntukan'        => trim($row[$map['peruntukan']] ?? null),
                'keterangan'        => trim($row[$map['keterangan']] ?? null),
            ];
            
            $asset = null;
            if (!empty($assetData['serial_number'])) {
                $asset = Asset::withTrashed()->where('serial_number', $assetData['serial_number'])->first();
            }
            if (!$asset && !empty($assetData['code_asset'])) {
                $asset = Asset::withTrashed()->where('code_asset', $assetData['code_asset'])->first();
            }

            if ($asset) {
                $asset->update($assetData);
                if ($asset->trashed()) {
                    $asset->restore();
                }
            } else {
                if (!empty($assetData['code_asset'])) {
                    Asset::create($assetData);
                }
            }
            
            if ($user && isset($asset)) {
                $this->updateUserHistory($asset, $user->id);
            }
        }
    }

    private function getColumnMapping(Collection $keys): ?array
    {
        $headers = $keys->map(fn($item) => Str::snake($item));

        // Peta untuk format file dari hasil EKSPOR INTERNAL (SUDAH LENGKAP)
        $internalExportMap = [
            'code_asset' => 'kode_aset', 'nama_barang' => 'nama_barang', 'kategori' => 'kategori',
            'sub_kategori' => 'sub_kategori', 'perusahaan' => 'perusahaan', 'merk' => 'merk', 'tipe' => 'tipe',
            'serial_number' => 'serial_number', 'pengguna' => 'pengguna_saat_ini', 'jabatan' => 'jabatan_pengguna',
            'departemen' => 'departemen_pengguna', 'kondisi' => 'kondisi', 'lokasi' => 'lokasi_fisik',
            'jumlah' => 'jumlah', 'satuan' => 'satuan', 'processor' => 'processor', 'ram' => 'ram',
            'storage' => 'storage', 'graphics' => 'graphics', 'layar' => 'layar',
            'nomor_polisi' => 'nomor_polisi', 'nomor_rangka' => 'nomor_rangka', 'nomor_mesin' => 'nomor_mesin',
            'deskripsi' => 'spesifikasi_deskripsi_lainnya', 'tanggal_pembelian' => 'tanggal_pembelian',
            'tahun_pembelian' => 'tahun_pembelian', 'harga_total' => 'harga_total_rp', 'po_number' => 'nomor_po',
            'nomor_bast' => 'nomor_bast', 'code_aktiva' => 'kode_aktiva', 'sumber_dana' => 'sumber_dana',
            'item_termasuk' => 'item_termasuk', 'peruntukan' => 'peruntukan', 'keterangan' => 'keterangan',
        ];

        // Peta untuk format file "CONTOH REPORT" (PERLU PENYESUAIAN)
        $contohReportMap = [
            'code_asset' => 'code_asset', 'nama_barang' => 'nama_item', 'kategori' => 'kategori_barang',
            'sub_kategori' => 'jenis', 'perusahaan' => 'milik_perusahaan', 'merk' => 'merk', 'tipe' => 'tipe',
            'serial_number' => 'serial_number', 'pengguna' => 'nama_2', 'jabatan' => 'jabatan_2',
            'departemen' => 'departemen_2', 'kondisi' => 'kondisi', 'lokasi' => 'lokasi',
            'jumlah' => 'jumlah', 'satuan' => 'satuan', 'processor' => 'processor', 'ram' => 'ram',
            'storage' => 'hddssd', 'graphics' => 'vga', 'layar' => 'lcd',
            'nomor_polisi' => 'nopol', 'nomor_rangka' => 'nomor_rangka', 'nomor_mesin' => 'nomor_mesin',
            'deskripsi' => 'keterangan_spesifikasi', 'tanggal_pembelian' => 'tanggal',
            'tahun_pembelian' => 'tahun', 'harga_total' => 'harga', 'po_number' => 'no_po',
            'nomor_bast' => 'no_bast', 'code_aktiva' => 'kode_aktiva', 'sumber_dana' => 'sumber_dana',
            'item_termasuk' => 'kelengkapan', 'peruntukan' => 'peruntukan', 'keterangan' => 'keterangan',
        ];

        if ($headers->contains('kode_aset')) {
            return $internalExportMap;
        }
        if ($headers->contains('nama_item')) {
            return $contohReportMap;
        }
        return null;
    }

    private function collectSpecifications(Collection $row, array $map): array
    {
        $specs = [];
        // Daftar semua kemungkinan kunci spesifikasi dari kedua peta
        $specKeys = [
            'processor', 'ram', 'storage', 'graphics', 'layar', 'nomor_polisi', 
            'nomor_rangka', 'nomor_mesin', 'deskripsi'
        ];

        foreach ($specKeys as $key) {
            // Cek apakah kunci ada di peta dan ada nilainya di baris excel
            if (isset($map[$key]) && !empty($row[$map[$key]])) {
                $specs[$key] = trim($row[$map[$key]]);
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

    public function chunkSize(): int
    {
        return 100;
    }
}