<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InventorySummaryExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        // Pindahkan total ke bawah secara manual jika diperlukan
        $totalAssets = $this->data->sum('total_assets');
        $totalValue = $this->data->sum('total_value');
        
        $collection = $this->data->map(function ($item) {
            return [
                'Kategori' => $item->category_name,
                'Kondisi' => $item->kondisi,
                'Jumlah Aset' => $item->total_assets,
                'Total Nilai (Rp)' => $item->total_value,
            ];
        });

        // Tambahkan baris total
        $collection->push([
            'Kategori' => 'TOTAL KESELURUHAN',
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
            'Kondisi',
            'Jumlah Aset',
            'Total Nilai (Rp)',
        ];
    }
}