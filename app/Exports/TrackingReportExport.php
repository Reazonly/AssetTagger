<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TrackingReportExport implements FromCollection, WithHeadings
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
            'Kondisi',
        ];
    }
}