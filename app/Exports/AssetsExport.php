<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class AssetsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $search;
    protected $assetIds;
    protected $categoryCode;
    protected $specKeys;

    public function __construct($search, $assetIds, $categoryCode)
    {
        $this->search = $search;
        $this->assetIds = $assetIds;
        $this->categoryCode = $categoryCode;
        $this->specKeys = $this->getUniqueSpecKeys();
    }

    private function getUniqueSpecKeys()
    {
        $assets = $this->query()->get();
        $keys = [];
        foreach ($assets as $asset) {
            if (is_array($asset->specifications)) {
                $keys = array_merge($keys, array_keys($asset->specifications));
            }
        }
        return array_unique($keys);
    }

    public function query()
    {
        // Eager load semua relasi yang dibutuhkan, termasuk history
        return Asset::query()->with(['category', 'subCategory', 'company', 'assetUser.company', 'history.assetUser']);
    }

    public function headings(): array
    {
        $baseHeadings = [
            'kode_aset', 'nama_barang', 'kategori', 'sub_kategori', 'perusahaan_pemilik',
            'merk', 'tipe', 'serial_number', 'pengguna_aset', 'jabatan_pengguna',
            'departemen_pengguna', 'perusahaan_pengguna', 'kondisi', 'lokasi', 'jumlah',
            'satuan', 'tanggal_pembelian', 'harga_total_rp', 'nomor_po', 'nomor_bast',
            'kode_aktiva', 'sumber_dana', 'item_termasuk', 'peruntukan', 'keterangan',
            'riwayat_pengguna' // Menambahkan kolom riwayat
        ];
        return array_merge($baseHeadings, $this->specKeys);
    }

    public function map($asset): array
    {
        // Format riwayat pengguna menjadi satu string
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
            optional($asset->tanggal_pembelian)->format('Y-m-d'),
            $asset->harga_total,
            $asset->po_number,
            $asset->nomor,
            $asset->code_aktiva,
            $asset->sumber_dana,
            $asset->include_items,
            $asset->peruntukan,
            $asset->keterangan,
            $historyString, // Menambahkan data riwayat
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
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
