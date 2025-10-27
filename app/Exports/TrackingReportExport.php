<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles; // Import interface baru
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Import trait baru
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class TrackingReportExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($asset) {
            return [
                'Kode Aset' => $asset->code_asset,
                'Nama Aset' => $asset->nama_barang,
                'Kategori' => optional($asset->category)->name,
                'Pengguna Saat Ini' => optional($asset->assetUser)->nama ?? 'N/A',
                'Jabatan Pengguna' => optional($asset->assetUser)->jabatan ?? 'N/A',
                'Perusahaan Pengguna' => optional(optional($asset->assetUser)->company)->name ?? 'N/A',
                'Lokasi Fisik' => $asset->lokasi ?? 'N/A',
                'Kondisi' => $asset->kondisi,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Kode Aset',
            'Nama Aset',
            'Kategori',
            'Pengguna Saat Ini',
            'Jabatan Pengguna',
            'Perusahaan Pengguna',
            'Lokasi Fisik',
            'Kondisi',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        $columnCount = count($this->headings());
        $lastColumn = Coordinate::stringFromColumnIndex($columnCount);

        return [
            // Style untuk Baris 1 (Header)
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '28A745']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Style untuk alignment vertikal dan horizontal seluruh data
            'A:' . $lastColumn => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // Override: Kode Aset dan Nama Aset lebih baik rata kiri
            'A:B' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ]
            ],
            // Override: Jabatan & Perusahaan lebih baik rata kiri
            'E:F' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ]
            ],
        ];
    }
}