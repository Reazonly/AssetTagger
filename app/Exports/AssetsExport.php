<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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

    public function query()
    {
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

    public function headings(): array
    {
        return [
            'Kode Aset', 'Nama Barang', 'Kategori', 'Sub Kategori', 'Perusahaan',
            'Merk', 'Tipe', 'Serial Number', 'Pengguna Saat Ini', 'Jabatan Pengguna',
            'Departemen Pengguna', 'Kondisi', 'Lokasi Fisik', 'Jumlah', 'Satuan',
            'Processor', 'RAM', 'Storage', 'Graphics', 'Layar',
            'Nomor Polisi', 'Nomor Rangka', 'Nomor Mesin',
            'Spesifikasi/Deskripsi Lainnya',
            'Tanggal Pembelian', 'Tahun Pembelian', 'Harga Total (Rp)', 'Nomor PO',
            'Nomor BAST', 'Kode Aktiva', 'Sumber Dana', 'Item Termasuk', 'Peruntukan', 'Keterangan'
        ];
    }

    public function map($asset): array
    {
        $specs = $asset->specifications ?? [];

        return [
            $asset->code_asset,
            $asset->nama_barang,
            optional($asset->category)->name,
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
            $specs['processor'] ?? null,
            $specs['ram'] ?? null,
            $specs['storage'] ?? null,
            $specs['graphics'] ?? null,
            $specs['layar'] ?? null,
            $specs['nomor_polisi'] ?? null,
            $specs['nomor_rangka'] ?? null,
            $specs['nomor_mesin'] ?? null,
            $specs['deskripsi'] ?? $specs['lainnya'] ?? null,
            $asset->tanggal_pembelian ? $asset->tanggal_pembelian->format('d-m-Y') : null,
            $asset->thn_pembelian,
            $asset->harga_total,
            $asset->po_number,
            $asset->nomor,
            $asset->code_aktiva,
            $asset->sumber_dana,
            $asset->include_items,
            $asset->peruntukan,
            $asset->keterangan,
        ];
    }

    /**
    * Memberi warna dan gaya pada baris judul.
    */
    public function styles(Worksheet $sheet)
    {
        return [
            // Terapkan gaya pada baris pertama (baris judul)
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'], // Warna teks putih
                ],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    // PERUBAHAN: Warna latar diubah menjadi hijau
                    'startColor' => ['rgb' => '28A745'], 
                ],
                // PERUBAHAN: Teks judul dibuat rata tengah (center)
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}