<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\AssetUser;
use App\Models\Category;
use App\Models\Company;
use App\Models\SubCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;

class AssetsImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private $companiesByName;
    private $companiesByCode;
    private $categories;
    private $subCategories;

    public function __construct()
    {
        $this->companiesByName = Company::all()->keyBy('name');
        $this->companiesByCode = Company::all()->keyBy('code');
        $this->categories = Category::all()->keyBy('name');
        $this->subCategories = SubCategory::all()->keyBy('name');
    }

    public function collection(Collection $rows)
    {
        $map = $this->getColumnMapping(collect($rows->first())->keys());
        if (!$map) return;

        foreach ($rows as $row) {
            $row = collect($row)->keyBy(fn($value, $key) => Str::snake($key));

            if (empty($row[$map['nama_barang']])) continue;
            
            $category = null;
            $subCategory = $this->subCategories[trim($row[$map['sub_kategori']] ?? '')] ?? null;

            if (isset($map['kategori']) && !empty($row[$map['kategori']])) {
                $category = $this->categories[trim($row[$map['kategori']])] ?? null;
            } elseif ($subCategory) {
                $category = $subCategory->category;
            }

            $company = $this->companiesByName[trim($row[$map['perusahaan']] ?? '')] ?? null;
            if (!$company) {
                 $codeParts = explode('/', trim($row[$map['code_asset']] ?? ''));
                 $companyCode = $codeParts[2] ?? null;
                 $company = $this->companiesByCode[$companyCode] ?? null;
            }

            $assetUser = null;
            if (!empty($row[$map['pengguna']])) {
                $namaPengguna = trim($row[$map['pengguna']]);
                $assetUser = AssetUser::firstOrCreate(
                    ['nama' => $namaPengguna],
                    [
                        'jabatan' => trim($row[$map['jabatan']] ?? null), 
                        'departemen' => trim($row[$map['departemen']] ?? null),
                    ]
                );
            }
            
            $specifications = $this->collectSpecifications($row, $map);
            $hargaTotal = trim($row[$map['harga_total']] ?? '');
            $thnPembelian = trim($row[$map['tahun_pembelian']] ?? '');
            
            $assetData = [
                'code_asset'        => trim($row[$map['code_asset']] ?? null),
                'nama_barang'       => trim($row[$map['nama_barang']]),
                'category_id'       => optional($category)->id,
                'sub_category_id'   => optional($subCategory)->id,
                'company_id'        => optional($company)->id,
                'asset_user_id'     => optional($assetUser)->id,
                'merk'              => trim($row[$map['merk']] ?? null),
                'tipe'              => trim($row[$map['tipe']] ?? null),
                'serial_number'     => trim($row[$map['serial_number']] ?? null),
                'kondisi'           => trim($row[$map['kondisi']] ?? 'Baik'),
                'lokasi'            => trim($row[$map['lokasi']] ?? null),
                'jumlah'            => trim($row[$map['jumlah']] ?? 1),
                'satuan'            => trim($row[$map['satuan']] ?? 'Unit'),
                'specifications'    => $specifications,
                'tanggal_pembelian' => $this->parseDate($row, $map),
                'thn_pembelian'     => !empty($thnPembelian) && is_numeric($thnPembelian) ? $thnPembelian : null,
                'harga_total'       => !empty($hargaTotal) && is_numeric($hargaTotal) ? $hargaTotal : null,
                'po_number'         => trim($row[$map['po_number']] ?? null),
                'nomor'             => trim($row[$map['nomor_bast']] ?? null),
                'code_aktiva'       => trim($row[$map['code_aktiva']] ?? null),
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
                if ($asset->trashed()) $asset->restore();
            } else {
                if (!empty($assetData['code_asset'])) {
                   $asset = Asset::create($assetData);
                }
            }

            // --- PERBAIKAN: Menambahkan pembuatan history saat import ---
            if ($asset && $assetUser) {
                // Cek riwayat terakhir untuk aset ini
                $latestHistory = $asset->history()->latest('tanggal_mulai')->first();

                // Hanya buat history baru jika:
                // 1. Belum ada history sama sekali, ATAU
                // 2. Pengguna di file Excel berbeda dengan pengguna di history terakhir
                if (!$latestHistory || $latestHistory->asset_user_id != $assetUser->id) {
                    
                    // Jika ada history sebelumnya, tutup tanggalnya (opsional, tergantung kebutuhan)
                    if ($latestHistory) {
                        $latestHistory->update(['tanggal_selesai' => now()]);
                    }
                    
                    // Buat catatan history baru untuk pengguna dari file Excel
                    $asset->history()->create([
                        'asset_user_id' => $assetUser->id,
                        'tanggal_mulai' => now(), // Diasumsikan serah terima terjadi saat import
                    ]);
                }
            }
            // --- AKHIR PERBAIKAN ---
        }
    }

    private function getColumnMapping(Collection $keys): ?array
    {
        $headers = $keys->map(fn($item) => Str::snake($item));

        $internalExportMap = [
            'code_asset' => 'kode_aset', 'nama_barang' => 'nama_barang', 'kategori' => 'kategori',
            'sub_kategori' => 'sub_kategori', 'perusahaan' => 'perusahaan', 'merk' => 'merk', 'tipe' => 'tipe',
            'serial_number' => 'serial_number', 'pengguna' => 'pengguna_saat_ini', 'jabatan' => 'jabatan_pengguna',
            'departemen' => 'departemen_pengguna', 'kondisi' => 'kondisi', 'lokasi' => 'lokasi_fisik',
            'jumlah' => 'jumlah', 'satuan' => 'satuan',
            'processor' => 'processor', 'ram' => 'ram', 'storage' => 'storage', 'graphics' => 'graphics', 'layar' => 'layar',
            'deskripsi' => 'spesifikasi_deskripsi_lainnya', 'tanggal_pembelian' => 'tanggal_pembelian',
            'tahun_pembelian' => 'tahun_pembelian', 'harga_total' => 'harga_total_rp', 'po_number' => 'nomor_po',
            'nomor_bast' => 'nomor_bast', 'code_aktiva' => 'kode_aktiva', 'sumber_dana' => 'sumber_dana',
            'item_termasuk' => 'item_termasuk', 'peruntukan' => 'peruntukan', 'keterangan' => 'keterangan',
        ];

        $contohReportMap = [
            'code_asset' => 'code_asset', 'nama_barang' => 'nama_item', 'sub_kategori' => 'jenis',
            'perusahaan' => 'perusahaan', 'merk' => 'merk', 'tipe' => 'type', 'serial_number' => 'serial_number',
            'pengguna' => 'nama_2', 'jabatan' => 'jabatan_2', 'departemen' => 'departemen_2',
            'kondisi' => 'kondisi', 'lokasi' => 'lokasi', 'jumlah' => 'qty', 'satuan' => 'unit',
            'processor' => 'processor', 'ram' => 'memory_ram',
            'storage' => 'hddssd', 'graphics' => 'graphics', 'layar' => 'lcd',
            'tahun_pembelian' => 'tahun',
            'tgl' => 'tgl', 'bulan' => 'bulan', 'tahun' => 'tahun', 'harga_total' => 'harga_total',
            'po_number' => 'po', 'nomor_bast' => 'nomor', 'code_aktiva' => 'code_aktiva',
            'item_termasuk' => 'include', 'peruntukan' => 'peruntukan', 'keterangan' => 'keterangan',
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
        $specKeys = ['processor', 'ram', 'storage', 'graphics', 'layar', 'deskripsi'];
        foreach ($specKeys as $key) {
            if (isset($map[$key]) && !empty($row[$map[$key]])) {
                $specs[$key] = trim($row[$map[$key]]);
            }
        }
        return $specs;
    }
    
    private function parseDate(Collection $row, array $map): ?string
    {
        if (isset($map['tanggal_pembelian']) && !empty($row[$map['tanggal_pembelian']])) {
            try {
                return Carbon::createFromFormat('d-m-Y', $row[$map['tanggal_pembelian']])->toDateString();
            } catch (\Exception $e) { return null; }
        }
        if (isset($map['tgl']) && !empty($row[$map['tgl']])) {
            try {
                $monthNames = [
                    'januari' => '01', 'februari' => '02', 'maret' => '03', 'april' => '04', 'mei' => '05', 'juni' => '06',
                    'juli' => '07', 'agustus' => '08', 'september' => '09', 'oktober' => '10', 'november' => '11', 'desember' => '12',
                ];
                $monthNum = $monthNames[strtolower(trim($row[$map['bulan']]))] ?? Carbon::parse($row[$map['bulan']])->month;
                return Carbon::createFromDate($row[$map['tahun']], $monthNum, $row[$map['tgl']])->toDateString();
            } catch (\Exception $e) { return null; }
        }
        return null;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
