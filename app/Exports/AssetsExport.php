<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class AssetsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $query;
    protected $specKeys;

    public function __construct(Builder $query)
    {
        $this->query = $query;
        $this->specKeys = $this->getUniqueSpecKeys();
    }

    private function getUniqueSpecKeys()
    {
        $assets = (clone $this->query)->get();
        $keys = [];
        foreach ($assets as $asset) {
            if (is_array($asset->specifications)) {
                $keys = array_merge($keys, array_keys($asset->specifications));
            }
        }
        
       
        $unwantedKeys = ['perusahaan_pemilik', 'perusahaan_pengguna'];
        $uniqueKeys = array_unique($keys);
        
        return array_values(array_diff($uniqueKeys, $unwantedKeys));
       
    }

    public function query()
    {
        return $this->query->with(['category', 'subCategory', 'company', 'assetUser.company', 'history.assetUser']);
    }

    public function headings(): array
    {
        $baseHeadings = [
            'Kode Aset', 'Nama Barang', 'Kategori', 'Sub Kategori', 'Perusahaan Pemilik (Kode)',
            'Merk', 'Tipe', 'Serial Number', 'Pengguna Aset', 'Jabatan Pengguna',
            'Departemen Pengguna', 'Perusahaan Pengguna (Kode)', 'Kondisi', 'Lokasi', 'Jumlah',
            'Satuan', 'Tanggal Pembelian', 'Harga Total (Rp)', 'Nomor PO', 'Nomor BAST',
            'Kode Aktiva', 'Sumber Dana', 'Item Termasuk', 'Peruntukan', 'Keterangan',
            'Riwayat Pengguna'
        ];

        return array_merge($baseHeadings, $this->specKeys);
    }

    public function map($asset): array
    {
        $historyString = $asset->history->map(function ($h) {
            $startDate = Carbon::parse($h->tanggal_mulai)->format('d M Y');
            $endDate = $h->tanggal_selesai ? Carbon::parse($h->tanggal_selesai)->format('d M Y') : 'Sekarang';
            $userName = optional($h->assetUser)->nama ?? 'N/A';
            return "{$userName} ({$startDate} - {$endDate})";
        })->implode('; ');

        $baseData = [
            $asset->code_asset,
            $asset->nama_barang,
            optional($asset->category)->name,
            optional($asset->subCategory)->name,
            optional($asset->company)->code,
            $asset->merk,
            $asset->tipe,
            $asset->serial_number,
            optional($asset->assetUser)->nama,
            optional($asset->assetUser)->jabatan,
            optional($asset->assetUser)->departemen,
            optional(optional($asset->assetUser)->company)->code,
            $asset->kondisi,
            $asset->lokasi,
            $asset->jumlah,
            $asset->satuan,
            optional($asset->tanggal_pembelian)->format('Y-m-d'),
            $asset->harga_total,
            $asset->po_number,
            $asset->nomor,
            $asset->code_aktiva,
            $asset->sumber_dana,
            $asset->include_items,
            $asset->peruntukan,
            $asset->keterangan,
            $historyString,
        ];

        $specData = [];
        foreach ($this->specKeys as $key) {
            $specData[] = $asset->specifications[$key] ?? '';
        }

        return array_merge($baseData, $specData);
    }

    public function styles(Worksheet $sheet)
    {
        $columnCount = count($this->headings());
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount);

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '28A745']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
            'A:' . $lastColumn => [
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}
