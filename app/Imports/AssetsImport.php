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
    private $subCategories;

    public function __construct()
    {
        // Siapkan data master untuk pencarian cepat
        $this->companies = Company::all()->keyBy('code');
        $this->subCategories = SubCategory::all()->keyBy('name');
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            if (empty($row['nama_item'])) {
                continue;
            }

            // --- Logika Cerdas Berdasarkan Data Anda ---
            // 1. Cari Sub-Kategori & Kategori Induknya
            $subCategory = $this->subCategories[strtoupper(trim($row['jenis'] ?? ''))] ?? null;
            $category = $subCategory ? $subCategory->category : null;

            // 2. Cari Perusahaan dari Kode Aset (e.g., LAPT/LENO/JG/379 -> JG)
            $codeParts = explode('/', trim($row['code_asset'] ?? ''));
            $companyCode = $codeParts[2] ?? null;
            $company = $this->companies[$companyCode] ?? null;

            // 3. Cari atau buat Pengguna
            $user = null;
            if (!empty($row['nama_2'])) {
                $namaPengguna = trim($row['nama_2']);
                $user = User::updateOrCreate(
                    ['nama_pengguna' => $namaPengguna],
                    [
                        'email' => Str::slug($namaPengguna) . '_' . time() . '@jhonlin.local',
                        'password' => Hash::make(Str::random(12)),
                        'jabatan' => trim($row['jabatan_2'] ?? null), 
                        'departemen' => trim($row['departemen_2'] ?? null)
                    ]
                );
            }
            
            // 4. Siapkan semua data aset berdasarkan header file Anda
            $assetData = [
                'code_asset'        => trim($row['code_asset'] ?? null),
                'nama_barang'       => trim($row['nama_item'] ?? null),
                'category_id'       => optional($category)->id,
                'sub_category_id'   => optional($subCategory)->id,
                'company_id'        => optional($company)->id,
                'merk'              => explode(' ', trim($row['nama_item'] ?? ''))[1] ?? null, // Ambil merk dari nama_item
                'tipe'              => trim($row['type'] ?? null),
                'serial_number'     => trim($row['serial_number'] ?? null),
                'kondisi'           => trim($row['kondisi'] ?? 'BAIK'),
                'lokasi'            => trim($row['lokasi'] ?? null),
                'jumlah'            => is_numeric($row['jumlah'] ?? null) ? $row['jumlah'] : 1,
                'satuan'            => trim($row['satuan'] ?? 'Unit'),
                'user_id'           => optional($user)->id,
                'specifications'    => $this->collectSpecifications($row),
                'tanggal_pembelian' => $this->parseDate($row['tgl'], $row['bulan'], $row['tahun']),
                'thn_pembelian'     => trim($row['tahun'] ?? null),
                'harga_total'       => is_numeric($row['harga_total'] ?? null) ? $row['harga_total'] : null,
                'po_number'         => trim($row['po'] ?? null),
                'nomor'             => trim($row['nomor'] ?? null), // Nomor BAST
                'code_aktiva'       => trim($row['code_aktiva'] ?? null),
                'sumber_dana'       => null, // Tidak ada di file Anda
                'include_items'     => trim($row['include'] ?? null),
                'peruntukan'        => trim($row['peruntukan'] ?? null),
                'keterangan'        => trim($row['keterangan'] ?? null),
            ];
            
            // 5. Simpan atau perbarui data
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
                    $asset = Asset::create($assetData);
                }
            }
            
            if ($user && $asset) {
                $this->updateUserHistory($asset, $user->id);
            }
        }
    }

    private function collectSpecifications(Collection $row): array
    {
        $specs = [];
        $specMap = [
            'processor' => 'processor', 'memory_ram' => 'ram', 'hddssd' => 'storage',
            'graphics' => 'graphics', 'lcd' => 'layar',
        ];

        foreach ($specMap as $key => $specName) {
            if (!empty($row[$key])) {
                $specs[$specName] = trim($row[$key]);
            }
        }
        return $specs;
    }

    private function parseDate($day, $month, $year)
    {
        if (empty($day) || empty($month) || empty($year)) return null;
        try {
            $monthNames = [
                'januari' => '01', 'februari' => '02', 'maret' => '03', 'april' => '04', 'mei' => '05', 'juni' => '06',
                'juli' => '07', 'agustus' => '08', 'september' => '09', 'oktober' => '10', 'november' => '11', 'desember' => '12',
            ];
            $monthNum = $monthNames[strtolower(trim($month))] ?? Carbon::parse($month)->month;
            return Carbon::createFromDate($year, $monthNum, $day)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
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