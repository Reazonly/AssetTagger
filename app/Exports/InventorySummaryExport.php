<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;

class InventorySummaryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $data;
    protected $specKeys;
    protected $totalHarga;
    protected $assetCount; 

    public function __construct(Collection $data)
    {
        $this->data = $data;
        $this->totalHarga = $this->data->sum('harga_total'); 
        $this->assetCount = $this->data->count();
        $this->specKeys = $this->getUniqueSpecKeys();
    }

    private function getUniqueSpecKeys()
    {
        $keys = [];
        foreach ($this->data as $asset) {
            if (is_array(optional($asset)->specifications)) {
                $keys = array_merge($keys, array_keys($asset->specifications));
            }
        }
        $unwantedKeys = ['perusahaan_pemilik', 'perusahaan_pengguna'];
        $uniqueKeys = array_unique($keys);
        return array_values(array_diff($uniqueKeys, $unwantedKeys));
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        $baseHeadings = [
            'Kode Aset',
            'Nama Barang',
            'SN',
            'Kategori',
            'Sub-Kategori',
            'Pengguna Saat Ini',
            'Perusahaan Pengguna', 
            'Jabatan Pengguna', 
            'Departemen Pengguna', 
            'Perusahaan Pemilik',
            'Lokasi', 
            'Kondisi', // Index 11 (Kolom L)
            // 'Harga Satuan (Rp)' dihapus
            'Harga Total (Rp)',  // Index 12 (Kolom M)
            'Tgl Perolehan',     // Index 13 (Kolom N)
            'No PO',
            'Kode Aktiva',
            'Sumber Dana',
            'Keterangan',
        ];
        return array_merge($baseHeadings, $this->specKeys);
    }

    public function map($asset): array
    {
        // Akses Relasi Kategori & Sub-Kategori
        $kategoriName = optional(optional($asset->subCategory)->category)->name ?? 'N/A';
        $subCategoryName = optional($asset->subCategory)->name ?? 'N/A';
        
        // Akses Data Pengguna
        $assetUser = optional($asset->assetUser);
        $assetUserName = $assetUser->nama ?? 'Stok';
        $userCompanyName = optional(optional($assetUser)->company)->name ?? ($assetUser ? '-' : 'N/A');
        $userJabatan = $assetUser->jabatan ?? ($assetUser ? '-' : 'N/A');
        $userDepartemen = $assetUser->departemen ?? ($assetUser ? '-' : 'N/A');
        
        // Akses Data Aset Lain
        $companyName = optional($asset->company)->name ?? 'N/A';
        $location = $asset->lokasi ?? $asset->location ?? 'N/A'; 
        
        // >>> PERBAIKAN KRITIS: Menggunakan tanggal_pembelian dari database
        $tanggalPembelian = $asset->tanggal_pembelian ? Carbon::parse($asset->tanggal_pembelian)->format('d-m-Y') : '-';

        // Data Utama
        $baseData = [
            $asset->code_asset ?? '-',
            $asset->nama_barang ?? '-',
            $asset->serial_number ?? '-',
            $kategoriName,
            $subCategoryName,
            $assetUserName,
            $userCompanyName, 
            $userJabatan, 
            $userDepartemen, 
            $companyName,
            $location, 
            $asset->kondisi ?? '-', 
            $asset->harga_total ?? 0, 
            $tanggalPembelian, // Menggunakan variabel yang sudah di mapping
            $asset->po_number ?? '-',
            $asset->code_aktiva ?? '-',
            $asset->sumber_dana ?? '-',
            $asset->keterangan ?? '-',
        ];

        // Data Spesifikasi Dinamis
        $specData = [];
        $assetSpecs = optional($asset)->specifications ?? [];
        foreach ($this->specKeys as $key) {
            $specData[] = $assetSpecs[$key] ?? '';
        }

        return array_merge($baseData, $specData);
    }

    public function styles(Worksheet $sheet)
    {
        // Styling Header (Baris 1)
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '28A745']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ],
        ];
    }
    
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $totalRow = $sheet->getHighestRow() + 1;
                $columnCount = count($this->headings());
                $lastColumn = Coordinate::stringFromColumnIndex($columnCount);

                // --- 1. PENENTUAN KOLOM UNTUK BARIS TOTAL (Indeks baru setelah Harga Satuan dihapus) ---
                $kondisiIndex = 11;             // Kolom L (Index 11)
                $hargaTotalIndex = 12;          // Kolom M (Index 12)
                $tglPerolehanIndex = 13;        // Kolom N (Index 13)
                
                // Kolom tempat "Jumlah Aset" akan diletakkan (di kolom Kondisi)
                $assetCountIndex = $kondisiIndex; 
                // Kolom terakhir yang digabung (Kolom Lokasi)
                $lastMergedIndex = $kondisiIndex - 1;      
                
                // Konversi Index ke Huruf Kolom (1-based)
                $assetCountColumn = Coordinate::stringFromColumnIndex($assetCountIndex + 1); // Column L
                $lastMergedColumn = Coordinate::stringFromColumnIndex($lastMergedIndex + 1);   // Column K
                $hargaTotalColumn = Coordinate::stringFromColumnIndex($hargaTotalIndex + 1);   // Column M
                $tglPerolehanColumn = Coordinate::stringFromColumnIndex($tglPerolehanIndex + 1); // Column N

                // 2. TAMBAHKAN BARIS TOTAL
                $dataTotal = array_fill(0, $columnCount, '');
                $dataTotal[0] = 'TOTAL KESELURUHAN';
                // Masukkan Jumlah Aset di kolom Kondisi (Index 11 / Kolom L)
                $dataTotal[$assetCountIndex] = 'Jumlah Aset: ' . number_format($this->assetCount, 0, ',', '.'); 
                // Masukkan Total Harga di kolom Harga Total (Index 12 / Kolom M)
                $dataTotal[$hargaTotalIndex] = $this->totalHarga; 
                
                // Masukkan data total
                $columnIndex = 1;
                foreach ($dataTotal as $value) {
                    $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
                    $sheet->setCellValue($columnLetter . $totalRow, $value);
                    $columnIndex++;
                }

                // 3. GABUNGKAN & STYLE BARIS TOTAL
                // Gabung sel dari A sampai kolom Lokasi (Column K)
                $sheet->mergeCells('A' . $totalRow . ':' . $lastMergedColumn . $totalRow); 
                
                $sheet->getStyle($totalRow)
                    ->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF3CD']],
                    ]);
                
                $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                
                // 4. TERAPKAN STYLE AKHIR DAN BORDER
                $fullRange = 'A1:' . $lastColumn . $totalRow;
                
                $sheet->getStyle($fullRange)
                    ->applyFromArray([
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                
                // --- 5. PERAPIHAN ALIGNMENT HORIZONTAL ---
                
                // ALIGNMENT RATA KIRI (Range: A sampai Kolom Lokasi (K), dan Kolom Tgl Perolehan (N) sampai akhir)
                $sheet->getStyle('A:' . $lastMergedColumn)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle($tglPerolehanColumn . ':' . $lastColumn)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); 

                // ALIGNMENT RATA KANAN (Untuk Angka/Mata Uang)
                $sheet->getStyle($hargaTotalColumn . ':' . $hargaTotalColumn)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // ALIGNMENT KHUSUS: Kolom Jumlah Aset di Baris Total (Kondisi)
                $sheet->getStyle($assetCountColumn . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // --- 6. FORMAT MATA UANG ---
                if ($totalRow > 1) {
                    // Harga Total: Baris Data + Baris Total (2 sampai Total Row)
                    $sheet->getStyle($hargaTotalColumn . '2:' . $hargaTotalColumn . $totalRow)
                        ->getNumberFormat()
                        ->setFormatCode('"Rp" #,##0.00_-');
                }

                // --- 7. LEBAR KOLOM KHUSUS ---
                $sheet->getColumnDimension($tglPerolehanColumn)->setWidth(18); 
                $sheet->getColumnDimension('A')->setWidth(18); 
                $sheet->getColumnDimension('B')->setWidth(30); 
                
                // Pastikan Kolom Kondisi (Jumlah Aset) cukup lebar
                $sheet->getColumnDimension($assetCountColumn)->setWidth(25);
            }
        ];
    }
}