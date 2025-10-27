<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles; 
use Maatwebsite\Excel\Concerns\ShouldAutoSize; 
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class InventorySummaryExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $totalAssets = $this->data->sum('total_assets');
        $totalValue = $this->data->sum('total_value');
        
        $collection = $this->data->map(function ($item) {
            return [
                'Kategori' => $item->category_name,
                'Sub-Kategori' => $item->sub_category_display_name, // PERUBAHAN DI SINI
                'Kondisi' => $item->kondisi,
                'Jumlah Aset' => $item->total_assets,
                'Total Nilai (Rp)' => $item->total_value,
            ];
        });

        // Tambahkan baris total
        $collection->push([
            'Kategori' => 'TOTAL KESELURUHAN',
            'Sub-Kategori' => '', // KOLOM BARU
            'Kondisi' => '',
            'Jumlah Aset' => $totalAssets,
            'Total Nilai (Rp)' => $totalValue,
        ]);
        
        return $collection;
    }

    public function headings(): array
    {
        return [
            'Kategori',
            'Sub-Kategori', // HEADER BARU
            'Kondisi',
            'Jumlah Aset',
            'Total Nilai (Rp)',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        $columnCount = count($this->headings()); // Sekarang harusnya 5
        $lastColumn = Coordinate::stringFromColumnIndex($columnCount); // Sekarang harusnya E

        // Styling untuk Baris Header (Baris 1)
        $styles = [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '28A745']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Styling untuk alignment vertikal seluruh data
            'A1:' . $lastColumn . $sheet->getHighestRow() => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Alignment default untuk seluruh kolom (Horizontal Center)
             'A:' . $lastColumn => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];

        // Override Alignment untuk kolom 'Kategori' (A) dan 'Sub-Kategori' (B)
        $styles['A:B'] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]];
        
        // Override Alignment untuk kolom 'Total Nilai' (E, karena ada 5 kolom sekarang)
        $styles['E:E'] = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]];

        // Terapkan format mata uang Indonesia untuk kolom 'Total Nilai (Rp)' (Kolom E)
        $sheet->getStyle('E2:E' . $sheet->getHighestRow())
              ->getNumberFormat()
              ->setFormatCode('"Rp" #,##0.00_-');

        return $styles;
    }
}