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
    protected ?string $categoryCode;

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
        // PERBAIKAN: Menambahkan 'subCategory' ke eager loading untuk efisiensi
        $query = Asset::with(['user', 'category', 'company', 'subCategory']);

        if (!empty($this->ids)) {
            return $query->whereIn('id', $this->ids)->latest();
        }

        if ($this->categoryCode) {
            $query->whereHas('category', function ($q) {
                $q->where('code', $this->categoryCode);
            });
        }

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
    * Mendefinisikan judul untuk setiap kolom di file Excel.
    */
    public function headings(): array
    {
        $baseHeadings = [
            'Kode Aset', 'Nama Barang', 'Kategori', 'Sub Kategori', 'Perusahaan',
            'Merk', 'Tipe', 'Serial Number', 'Pengguna Saat Ini', 'Jabatan Pengguna',
            'Departemen Pengguna', 'Kondisi', 'Lokasi Fisik', 'Jumlah', 'Satuan'
        ];

        $endHeadings = [
            'Tanggal Pembelian', 'Tahun Pembelian', 'Harga Total (Rp)', 'Nomor PO',
            'Nomor BAST', 'Kode Aktiva', 'Sumber Dana', 'Item Termasuk', 'Peruntukan', 'Keterangan'
        ];

        $specHeadings = [];
        switch ($this->categoryCode) {
            case 'ELEC':
                $specHeadings = ['Processor', 'RAM', 'Storage', 'Graphics', 'Layar', 'Spesifikasi Lainnya'];
                break;
            case 'VEHI':
                $specHeadings = ['Nomor Polisi', 'Nomor Rangka', 'Nomor Mesin', 'Spesifikasi Lainnya'];
                break;
            default:
                $specHeadings = ['Spesifikasi/Deskripsi'];
                break;
        }

        return array_merge($baseHeadings, $specHeadings, $endHeadings);
    }

    /**
    * Memetakan data dari setiap model Asset ke dalam format array.
    * @param Asset $asset
    */
    public function map($asset): array
    {
        $specs = $asset->specifications ?? [];

        $baseData = [
            $asset->code_asset,
            $asset->nama_barang,
            optional($asset->category)->name,
            // PERBAIKAN: Menggunakan relasi 'subCategory' untuk mendapatkan nama
            optional($asset->subCategory)->name,
            optional($asset->company)->name,
            $asset->merk,
            $asset->tipe,
            $asset->serial_number,
            optional($asset->user)->nama_pengguna,
            optional($asset->user)->jabatan,
            optional($asset->user)->departemen,
            $asset->kondisi,
            $asset->lokasi,
            $asset->jumlah,
            $asset->satuan,
        ];

        $endData = [
            $asset->tanggal_pembelian ? $asset->tanggal_pembelian->format('d-m-Y') : null,
            $asset->thn_pembelian,
            $asset->harga_total,
            $asset->po_number,
            $asset->nomor, // Nomor BAST
            $asset->code_aktiva,
            $asset->sumber_dana,
            $asset->include_items,
            $asset->peruntukan,
            $asset->keterangan,
        ];

        $specData = [];
        switch ($this->categoryCode) {
            case 'ELEC':
                $specData = [
                    $specs['processor'] ?? null,
                    $specs['ram'] ?? null,
                    $specs['storage'] ?? null,
                    $specs['graphics'] ?? null,
                    $specs['layar'] ?? null,
                    $specs['lainnya'] ?? null,
                ];
                break;
            case 'VEHI':
                $specData = [
                    $specs['nomor_polisi'] ?? null,
                    $specs['nomor_rangka'] ?? null,
                    $specs['nomor_mesin'] ?? null,
                    $specs['lainnya'] ?? null,
                ];
                break;
            default:
                $specData = [
                    $specs['deskripsi'] ?? null
                ];
                break;
        }
        
        return array_merge($baseData, $specData, $endData);
    }
}