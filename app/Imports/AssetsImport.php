<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class AssetsImport implements ToModel, WithHeadingRow, WithUpserts
{
    public function model(array $row)
    {
        if (empty($row['code_asset'])) {
            return null;
        }

        $user = null;
        if (!empty($row['nama_2'])) {
            $user = User::firstOrCreate(
                ['nama_pengguna' => trim($row['nama_2'])],
                ['jabatan' => trim($row['jabatan_2'] ?? null), 'departemen' => trim($row['departemen_2'] ?? null)]
            );
        }

        return new Asset([
            'code_asset'        => trim($row['code_asset']),
            'nama_barang'       => trim($row['nama_item']),
            'merk_type'         => trim($row['type'] ?? null),
            'serial_number'     => trim($row['serial_number'] ?? null),
            'user_id'           => $user ? $user->id : null,
            'processor'         => trim($row['processor'] ?? null),
            'memory_ram'        => trim($row['memory_ram'] ?? null),
            'hdd_ssd'           => trim($row['hdd_ssd'] ?? null),
            'graphics'          => trim($row['graphics'] ?? null),
            'lcd'               => trim($row['lcd'] ?? null),
            'thn_pembelian'     => $row['tahun'] ?? null,
            'po_number'         => $row['po'] ?? null,
            'harga_total'       => $row['harga_total'] ?? null,
            'sumber_dana'       => trim($row['sumber_dana'] ?? null),
            'code_aktiva'       => trim($row['code_aktiva'] ?? null),
            'kondisi'           => trim($row['kondisi'] ?? null),
            'lokasi'            => trim($row['lokasi'] ?? null),
            'jumlah'            => $row['jumlah'] ?? 1,
            'satuan'            => trim($row['satuan'] ?? 'UNIT'),
            'nomor'             => trim($row['nomor'] ?? null),
            'include_items'     => trim($row['include'] ?? null),
            'peruntukan'        => trim($row['peruntukan'] ?? null),
            'keterangan'        => trim($row['keterangan'] ?? null),
        ]);
    }
    
    public function uniqueBy()
    {
        return 'code_asset';
    }
}
