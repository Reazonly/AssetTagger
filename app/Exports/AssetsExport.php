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

    /**
     */
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
        return $this->query->with(['category', 'subCategory', 'company', 'assetUser.company']);
    }

    /**
     */
    public function headings(): array
    {
        $baseHeadings = [
            'Kode Aset',
            'Nama Barang', 'Kategori', 'Sub Kategori', 'Perusahaan Pemilik',
            'Merk', 'Tipe', 'Serial Number', 'Pengguna Aset', 'Jabatan Pengguna',
            'Departemen Pengguna', 'Perusahaan Pengguna', 'Kondisi', 'Lokasi', 'Jumlah',
            'Satuan', 
            'Tanggal Pembelian', 'Bulan Pembelian', 'Tahun Pembelian',
            'Harga Total (Rp)', 'Nomor PO', 'Nomor BAST',
            'Kode Aktiva', 'Sumber Dana', 'Item Termasuk', 'Peruntukan', 'Keterangan',
        ];

        return array_merge($baseHeadings, $this->specKeys);
    }

    /**
     * Memetakan data dari setiap aset ke dalam kolom yang sesuai.
     * @param Asset $asset
     * @return array
     */
    public function map($asset): array
    {
        $tanggal = $asset->tanggal_pembelian ? Carbon::parse($asset->tanggal_pembelian)->locale('id') : null;
        $tgl = $tanggal ? $tanggal->day : null;
        $bulan = $tanggal ? $tanggal->isoFormat('MMMM') : null; 
        $tahun = $tanggal ? $tanggal->year : null;

        $baseData = [
            $asset->code_asset, 
            $asset->nama_barang,
            optional($asset->category)->name,
            optional($asset->subCategory)->name,
            optional($asset->company)->name,
            $asset->merk,
            $asset->tipe,
            $asset->serial_number,
            optional($asset->assetUser)->nama,
            optional($asset->assetUser)->jabatan,
            optional($asset->assetUser)->departemen,
            optional(optional($asset->assetUser)->company)->name,
            $asset->kondisi,
            $asset->lokasi,
            $asset->jumlah,
            $asset->satuan,
            $tgl,
            $bulan,
            $tahun,
            $asset->harga_total,
            $asset->po_number,
            $asset->nomor, 
            $asset->code_aktiva,
            $asset->sumber_dana,
            $asset->include_items,
            $asset->peruntukan,
            $asset->keterangan,
        ];

       
        $specData = [];
        foreach ($this->specKeys as $key) {
            $specData[] = $asset->specifications[$key] ?? '';
        }

        return array_merge($baseData, $specData);
    }

    /**
     */
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