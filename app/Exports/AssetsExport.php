<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AssetsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected ?string $search;

    public function __construct(?string $search)
    {
        $this->search = $search;
    }

    /**
     * Menjalankan query untuk mendapatkan data aset dari database.
     * Menggunakan FromQuery lebih efisien untuk data besar.
     */
    public function query()
    {
        // Logika pencarian ini disalin dari AssetController untuk memastikan
        // data yang diekspor sama dengan yang ditampilkan di halaman.
        return Asset::with('user')
            ->when($this->search, function ($query, $searchTerm) {
                return $query->where('code_asset', 'like', "%{$searchTerm}%")
                             ->orWhere('nama_barang', 'like', "%{$searchTerm}%")
                             ->orWhereHas('user', function ($subQuery) use ($searchTerm) {
                                 $subQuery->where('nama_pengguna', 'like', "%{$searchTerm}%");
                             });
            })
            ->latest();
    }

    /**
     * Mendefinisikan judul kolom untuk file Excel.
     */
    public function headings(): array
    {
        return [
            'Kode Aset',
            'Nama Barang',
            'Merk/Tipe',
            'Serial Number',
            'Pengguna Saat Ini',
            'Jabatan Pengguna',
            'Departemen Pengguna',
            'Kondisi',
            'Lokasi Fisik',
            'Tanggal Pembelian',
            'Tahun Pembelian',
            'Harga Total (Rp)',
            'Nomor PO',
            'Kode Aktiva',
            'Keterangan',
        ];
    }

    /**
     * Memetakan data dari setiap model Asset ke dalam format array untuk setiap baris Excel.
     *
     * @param Asset $asset
     */
    public function map($asset): array
    {
        return [
            $asset->code_asset,
            $asset->nama_barang,
            $asset->merk_type,
            $asset->serial_number,
            $asset->user->nama_pengguna ?? 'N/A', // Mengambil nama pengguna dari relasi
            $asset->user->jabatan ?? 'N/A',
            $asset->user->departemen ?? 'N/A',
            $asset->kondisi,
            $asset->lokasi,
            $asset->tanggal_pembelian ? $asset->tanggal_pembelian->format('d-m-Y') : 'N/A', // Format tanggal
            $asset->thn_pembelian,
            $asset->harga_total,
            $asset->po_number,
            $asset->code_aktiva,
            $asset->keterangan,
        ];
    }
}