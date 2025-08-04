<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class AssetsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected ?string $search;
    protected ?array $ids;
    protected ?string $categoryCode; // Kode kategori yang akan diekspor, misal: 'ELEC', 'VEHI'

    public function __construct(?string $search = null, ?array $ids = null, ?string $categoryCode = null)
    {
        $this->search = $search;
        $this->ids = $ids;
        $this->categoryCode = $categoryCode;
    }

    /**
    * Menentukan query untuk mengambil data aset dari database.
    */
    public function query()
    {
        $query = Asset::with(['user', 'category', 'company']);

        // Jika ada ID yang dipilih, hanya ekspor aset-aset tersebut.
        if (!empty($this->ids)) {
            return $query->whereIn('id', $this->ids)->latest();
        }

        // Jika kategori spesifik diberikan, filter query berdasarkan kode kategori tersebut.
        if ($this->categoryCode) {
            $query->whereHas('category', function ($q) {
                $q->where('code', $this->categoryCode);
            });
        }

        // Terapkan filter pencarian jika ada.
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('code_asset', 'like', "%{$this->search}%")
                  ->orWhere('nama_barang', 'like', "%{$this->search}%")
                  ->orWhere('serial_number', 'like', "%{$this->search}%")
                  ->orWhereHas('user', function ($subQuery) {
                      $subQuery->where('nama_pengguna', 'like', "%{$this->search}%");
                  });
            });
        }

        return $query->latest();
    }

    /**
    * Mendefinisikan judul untuk setiap kolom di file Excel secara dinamis.
    */
    public function headings(): array
    {
        // Header dasar yang selalu ada
        $baseHeadings = [
            'Kode Aset', 'Nama Barang', 'Kategori', 'Sub Kategori', 'Perusahaan',
            'Merk', 'Tipe', 'Serial Number', 'Pengguna Saat Ini', 'Jabatan Pengguna',
            'Departemen Pengguna', 'Kondisi', 'Lokasi Fisik', 'Jumlah', 'Satuan'
        ];

        // Header akhir yang selalu ada
        $endHeadings = [
            'Tanggal Pembelian', 'Tahun Pembelian', 'Harga Total (Rp)', 'Nomor PO',
            'Nomor BAST', 'Kode Aktiva', 'Item Termasuk', 'Peruntukan', 'Keterangan'
        ];

        $specHeadings = [];
        // Tentukan header spesifikasi berdasarkan kode kategori
        switch ($this->categoryCode) {
            case 'ELEC':
                $specHeadings = ['Processor', 'RAM', 'Storage', 'Graphics', 'Layar'];
                break;
            case 'VEHI':
                $specHeadings = ['Tipe Mesin', 'CC Mesin', 'Bahan Bakar'];
                break;
            case null: // Jika tidak ada kategori, tampilkan semua kemungkinan header
                $specHeadings = [
                    'Processor', 'RAM', 'Storage', 'Graphics', 'Layar',
                    'Tipe Mesin', 'CC Mesin', 'Bahan Bakar', 'Spesifikasi/Deskripsi Lainnya'
                ];
                break;
            default: // Untuk kategori lain seperti Furniture
                $specHeadings = ['Spesifikasi/Deskripsi Lainnya'];
                break;
        }

        return array_merge($baseHeadings, $specHeadings, $endHeadings);
    }

    /**
    * Memetakan data dari setiap model Asset ke dalam format array secara dinamis.
    *
    * @param Asset $asset
    */
    public function map($asset): array
    {
        $specs = $asset->specifications ?? [];

        // Data dasar yang selalu ada
        $baseData = [
            $asset->code_asset,
            $asset->nama_barang,
            optional($asset->category)->name ?? 'N/A',
            $asset->sub_category ?? 'N/A',
            optional($asset->company)->name ?? 'N/A',
            $asset->merk ?? 'N/A',
            $asset->tipe ?? 'N/A',
            $asset->serial_number,
            optional($asset->user)->nama_pengguna ?? 'N/A',
            optional($asset->user)->jabatan ?? 'N/A',
            optional($asset->user)->departemen ?? 'N/A',
            $asset->kondisi,
            $asset->lokasi,
            $asset->jumlah,
            $asset->satuan,
        ];

        // Data akhir yang selalu ada
        $endData = [
            $asset->tanggal_pembelian ? $asset->tanggal_pembelian->format('d-m-Y') : 'N/A',
            $asset->thn_pembelian,
            $asset->harga_total,
            $asset->po_number,
            $asset->nomor, // Nomor BAST
            $asset->code_aktiva,
            $asset->include_items,
            $asset->peruntukan,
            $asset->keterangan,
        ];

        $specData = [];
        // Tentukan data spesifikasi berdasarkan kode kategori
        switch ($this->categoryCode) {
            case 'ELEC':
                $specData = [
                    $specs['processor'] ?? 'N/A',
                    $specs['ram'] ?? 'N/A',
                    $specs['storage'] ?? 'N/A',
                    $specs['graphics'] ?? 'N/A',
                    $specs['layar'] ?? 'N/A',
                ];
                break;
            case 'VEHI':
                $specData = [
                    $specs['tipe_mesin'] ?? 'N/A',
                    $specs['cc_mesin'] ?? 'N/A',
                    $specs['bahan_bakar'] ?? 'N/A',
                ];
                break;
            case null: // Jika tidak ada kategori, tampilkan semua kemungkinan data
                $specData = [
                    $specs['processor'] ?? 'N/A',
                    $specs['ram'] ?? 'N/A',
                    $specs['storage'] ?? 'N/A',
                    $specs['graphics'] ?? 'N/A',
                    $specs['layar'] ?? 'N/A',
                    $specs['tipe_mesin'] ?? 'N/A',
                    $specs['cc_mesin'] ?? 'N/A',
                    $specs['bahan_bakar'] ?? 'N/A',
                    $specs['deskripsi'] ?? $specs['lainnya'] ?? 'N/A',
                ];
                break;
            default: // Untuk kategori lain
                $specData = [
                    $specs['deskripsi'] ?? $specs['lainnya'] ?? 'N/A'
                ];
                break;
        }
        
        return array_merge($baseData, $specData, $endData);
    }
}
