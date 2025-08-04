<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\User;
use App\Models\Category;
use App\Models\Company;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;

class AssetsImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private $companies;
    private $categories;

    public function __construct()
    {
        // Pre-load master data untuk efisiensi
        $this->companies = Company::all()->keyBy('code');
        $this->categories = Category::all()->keyBy('name');
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            if (empty($row['nama_item'])) {
                continue;
            }

            // 1. Menentukan Kategori secara cerdas berdasarkan kolom 'jenis'
            $jenis = strtolower(trim($row['jenis'] ?? ''));
            $category = null;
            if (in_array($jenis, ['laptop', 'pc', 'printer', 'monitor', 'proyektor'])) {
                $category = $this->categories['Elektronik'] ?? null;
            } elseif (in_array($jenis, ['mobil', 'motor', 'alat berat'])) {
                $category = $this->categories['Kendaraan'] ?? null;
            } else {
                $category = $this->categories['Furniture'] ?? null;
            }

            // 2. Menentukan Perusahaan berdasarkan 'code_asset'
            $companyCode = explode('/', trim($row['code_asset'] ?? ''))[2] ?? null;
            $company = $this->companies[$companyCode] ?? null;

            // 3. Cari atau buat User berdasarkan kolom 'nama_2'
            $user = null;
            if (!empty($row['nama_2'])) {
                $user = User::firstOrCreate(
                    ['nama_pengguna' => trim($row['nama_2'])],
                    [
                        'jabatan' => trim($row['jabatan_2'] ?? null), 
                        'departemen' => trim($row['departemen_2'] ?? null)
                    ]
                );
            }
            
            // 4. Kumpulkan data spesifikasi dinamis
            $specifications = $this->collectSpecifications($row, $category);

            // 5. Siapkan data utama untuk aset
            $assetData = [
                'nama_barang'       => trim($row['nama_item']),
                'category_id'       => $category ? $category->id : null,
                'company_id'        => $company ? $company->id : null,
                'sub_category'      => trim($row['jenis'] ?? null),
                'merk'              => trim($row['type'] ?? null), // Menggunakan 'type' sebagai 'merk'
                'tipe'              => trim($row['type'] ?? null), // Juga menggunakan 'type' sebagai 'tipe'
                'serial_number'     => trim($row['serial_number'] ?? null),
                'kondisi'           => trim($row['kondisi'] ?? 'BAIK'),
                'lokasi'            => trim($row['lokasi'] ?? null),
                'jumlah'            => is_numeric($row['jumlah'] ?? null) ? $row['jumlah'] : 1,
                'satuan'            => trim($row['satuan'] ?? 'Unit'),
                'user_id'           => $user ? $user->id : null,
                'specifications'    => $specifications,
                'tanggal_pembelian' => $this->parseDate($row['tgl'], $row['bulan'], $row['tahun']),
                'thn_pembelian'     => trim($row['tahun'] ?? null),
                'harga_total'       => is_numeric($row['harga_total'] ?? null) ? $row['harga_total'] : null,
                'po_number'         => trim($row['po'] ?? null),
                'nomor'             => trim($row['nomor'] ?? null),
                'code_aktiva'       => trim($row['code_aktiva'] ?? null),
                'include_items'     => trim($row['include'] ?? null),
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
                $assetData['code_asset'] = trim($row['code_asset']);
                $asset = Asset::create($assetData);
            }
            
            // 7. Tangani pencatatan riwayat pengguna
            $this->updateUserHistory($asset, $user ? $user->id : null);
        }
    }

    private function collectSpecifications(Collection $row, ?Category $category): array
{
    $specs = [];
    if (!$category) {
        return $specs;
    }

    $specMap = [];
    // Kita gunakan 'jenis' yang ada di hasil debug Anda
    $subCategory = strtolower(trim($row['jenis'] ?? ''));

    if ($category->name === 'Elektronik') {
        switch ($subCategory) {
            case 'laptop':
            case 'pc':
                $specMap = [
                    'processor'  => 'processor', // Menggunakan kunci 'processor'
                    'memory_ram' => 'ram',       // Menggunakan kunci 'memory_ram'
                    'hddssd'     => 'storage',   // PERBAIKAN: Menggunakan kunci 'hddssd' yang kita temukan
                    'graphics'   => 'graphics',  // Menggunakan kunci 'graphics'
                    'lcd'        => 'layar',     // Menggunakan kunci 'lcd'
                ];
                break;
            case 'printer':
                $specMap = [
                    'tipe_printer'      => 'tipe_printer',
                    'kecepatan_cetak'   => 'kecepatan_cetak',
                    'resolusi_cetak'    => 'resolusi_cetak',
                    'konektivitas'      => 'konektivitas',
                ];
                break;
            case 'proyektor':
                $specMap = [
                    'teknologi' => 'teknologi',
                    'kecerahan' => 'kecerahan',
                    'resolusi'  => 'resolusi',
                ];
                break;
            default:
                $specMap = ['lainnya' => 'lainnya'];
                break;
        }
    } elseif ($category->name === 'Kendaraan') {
        $specMap = [
            'tipe_mesin'   => 'tipe_mesin',
            'cc_mesin'     => 'cc_mesin',
            'bahan_bakar'  => 'bahan_bakar',
            'lainnya'      => 'lainnya',
        ];
    } else {
        $specMap = ['deskripsi' => 'deskripsi'];
    }

    // --- LOGIKA UTAMA PERBAIKAN ---
   foreach ($specMap as $rowKey => $specKey) {
        if (!empty($row[$rowKey])) {
            $specs[$specKey] = trim($row[$rowKey]);
        }
    }

    return $specs;
}

    private function updateUserHistory(Asset $asset, ?int $newUserId): void
    {
        $latestHistory = $asset->history()->latest()->first();
        if (!$latestHistory || $latestHistory->user_id != $newUserId) {
            $asset->history()->whereNull('tanggal_selesai')->update(['tanggal_selesai' => now()]);
            if ($newUserId) {
                $asset->history()->create(['user_id' => $newUserId, 'tanggal_mulai' => now()]);
            }
        }
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

    public function chunkSize(): int
    {
        return 100;
    }
}
