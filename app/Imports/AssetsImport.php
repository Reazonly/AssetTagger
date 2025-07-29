<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class AssetsImport implements ToModel, WithHeadingRow, WithUpserts
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Lewati baris jika kode aset tidak ada (baris kosong)
        if (empty($row['code_asset'])) {
            return null;
        }

        // 1. Cari atau buat pengguna baru berdasarkan 'NAMA 2' dari Excel
        $user = null;
        if (!empty($row['nama_2'])) {
            $user = User::firstOrCreate(
                // Kunci untuk mencari pengguna
                ['nama_pengguna' => trim($row['nama_2'])],
                // Data untuk dibuat jika pengguna tidak ditemukan
                [
                    'jabatan' => $row['jabatan_2'] ?? null,
                    'departemen' => $row['departemen_2'] ?? null,
                ]
            );
        }

        // 2. Buat atau perbarui Aset dengan pemetaan kolom yang benar
        return new Asset([
            // Kolom di DB         => Kolom dari Excel (setelah diubah ke snake_case)
            'code_asset'        => $row['code_asset'],
            'nama_barang'       => $row['nama_item'], // <-- FIX: nama_barang -> nama_item
            'merk_type'         => $row['type'] ?? null, // <-- FIX: merk_type -> type
            'serial_number'     => $row['serial_number'] ?? null,
            'user_id'           => $user ? $user->id : null,
            'processor'         => $row['processor'] ?? null,
            'memory_ram'        => $row['memory_ram'] ?? null,
            'hdd_ssd'           => $row['hdd_ssd'] ?? null,
            'graphics'          => $row['graphics'] ?? null,
            'lcd'               => $row['lcd'] ?? null,
            'thn_pembelian'     => $row['tahun'] ?? null, // <-- FIX: thn_pembelian -> tahun
            'po_number'         => $row['po'] ?? null, // <-- FIX: po_number -> po
            'harga_total'       => $row['harga_total'] ?? null,
            'sumber_dana'       => $row['sumber_dana'] ?? null, // Kolom ini tidak ada di Excel Anda
            'code_aktiva'       => $row['code_aktiva'] ?? null,
            'kondisi'           => $row['kondisi'] ?? null,
            'lokasi'            => $row['lokasi'] ?? null,
            'jumlah'            => $row['jumlah'] ?? 1,
            'satuan'            => $row['satuan'] ?? 'UNIT',
            'nomor'             => $row['nomor'] ?? null,
            'include_items'     => $row['include'] ?? null, // <-- FIX: include_items -> include
            'peruntukan'        => $row['peruntukan'] ?? null,
            'keterangan'        => $row['keterangan'] ?? null,
        ]);
    }
    
    /**
     * Tentukan kolom unik untuk mencegah duplikasi data saat import.
     * Jika 'code_asset' sudah ada, data akan diupdate, bukan dibuat baru.
     *
     * @return string|array
     */
    public function uniqueBy()
    {
        return 'code_asset';
    }
}
