<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class AssetsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected ?string $search;
    protected ?array $ids;

    public function __construct(?string $search = null, ?array $ids = null)
    {
        $this->search = $search;
        $this->ids = $ids;
    }

    /**
    * Menentukan query untuk mengambil data aset dari database.
    */
    public function query()
    {
        // Eager load relasi untuk performa yang lebih baik (menghindari N+1 problem)
        $query = Asset::with(['user', 'category', 'company']);

        // Jika ada ID yang dipilih, hanya ekspor aset-aset tersebut.
        if (!empty($this->ids)) {
            return $query->whereIn('id', $this->ids)->latest();
        }

        // Jika tidak ada ID yang dipilih, gunakan filter pencarian.
        return $query->when($this->search, function ($q, $searchTerm) {
            return $q->where('code_asset', 'like', "%{$searchTerm}%")
                     ->orWhere('nama_barang', 'like', "%{$searchTerm}%")
                     ->orWhere('serial_number', 'like', "%{$searchTerm}%")
                     ->orWhereHas('user', function ($subQuery) use ($searchTerm) {
                         $subQuery->where('nama_pengguna', 'like', "%{$searchTerm}%");
                     });
        })->latest();
    }

    /**
    * Mendefinisikan judul untuk setiap kolom di file Excel.
    */
    public function headings(): array
    {
        return [
            'Kode Aset',
            'Nama Barang',
            'Kategori',
            'Sub Kategori',
            'Perusahaan',
            'Merk',
            'Tipe',
            'Serial Number',
            'Pengguna Saat Ini',
            'Jabatan Pengguna',
            'Departemen Pengguna',
            'Kondisi',
            'Lokasi Fisik',
            'Jumlah',
            'Satuan',
            'Processor',
            'RAM',
            'Storage',
            'Graphics',
            'Layar',
            'Spesifikasi/Deskripsi Lainnya',
            'Tanggal Pembelian',
            'Tahun Pembelian',
            'Harga Total (Rp)',
            'Nomor PO',
            'Nomor BAST',
            'Kode Aktiva',
            'Item Termasuk',
            'Peruntukan',
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
        // Ambil data spesifikasi dari kolom JSON, jika tidak ada, gunakan array kosong.
        $specs = $asset->specifications ?? [];

        return [
            $asset->code_asset,
            $asset->nama_barang,
            optional($asset->category)->name ?? 'N/A',
            $asset->sub_category ?? 'N/A',
            optional($asset->company)->name ?? 'N/A',
            $asset->merk ?? 'N/A',
            $asset->tipe ?? 'N/A',
            $asset->serial_number,
            optional($asset->user)->nama_pengguna ?? 'N/A',
            optional($asset->user)->jabatan ?? 'N/A',
            optional($asset->user)->departemen ?? 'N/A',
            $asset->kondisi,
            $asset->lokasi,
            $asset->jumlah,
            $asset->satuan,
            
            // Mengambil data dari array specifications dengan aman
            $specs['processor'] ?? 'N/A',
            $specs['ram'] ?? 'N/A',
            $specs['storage'] ?? 'N/A',
            $specs['graphics'] ?? 'N/A',
            $specs['layar'] ?? 'N/A',
            
            // Menggabungkan deskripsi atau spesifikasi lain untuk kolom manual
            $specs['deskripsi'] ?? $specs['lainnya'] ?? 'N/A',

            $asset->tanggal_pembelian ? $asset->tanggal_pembelian->format('d-m-Y') : 'N/A',
            $asset->thn_pembelian,
            $asset->harga_total,
            $asset->po_number,
            $asset->nomor, // Nomor BAST
            $asset->code_aktiva,
            $asset->include_items,
            $asset->peruntukan,
            $asset->keterangan,
        ];
    }
}
