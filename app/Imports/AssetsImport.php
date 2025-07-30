<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Carbon\Carbon;

class AssetsImport implements ToModel, WithHeadingRow, WithUpserts
{
    public function model(array $row)
    {
        if (empty($row['code_asset'])) {
            return null;
        }

        // 1. Proses tanggal menggunakan key yang benar dari header Excel
        $tanggalPembelian = $this->parseDate(
            $row['tgl'] ?? null,
            $row['bulan'] ?? null,
            $row['tahun'] ?? null
        );

        // 2. Cari atau buat pengguna baru
        $user = null;
        if (!empty($row['nama_2'])) {
            $user = User::firstOrCreate(
                ['nama_pengguna' => trim($row['nama_2'])],
                ['jabatan' => trim($row['jabatan_2'] ?? null), 'departemen' => trim($row['departemen_2'] ?? null)]
            );
        }

        // 3. Buat atau perbarui Aset
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
            'tanggal_pembelian' => $tanggalPembelian,
            'thn_pembelian'     => trim($row['tahun'] ?? null),
            'po_number'         => trim($row['po'] ?? null),
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
    
    /**
     * Fungsi yang sangat kuat untuk mengubah data tanggal terpisah menjadi format standar.
     */
    private function parseDate($day, $month, $year)
    {
        if (is_null($day) || is_null($month) || is_null($year)) {
            return null;
        }

        // 1. Membersihkan dan mengubah tipe data secara agresif
        $cleanedDay = (int) preg_replace('/[^\d]/', '', $day);
        $cleanedMonth = strtolower(trim($month));
        $cleanedYear = (int) preg_replace('/[^\d]/', '', $year);

        if (empty($cleanedDay) || empty($cleanedMonth) || empty($cleanedYear)) {
            return null;
        }

        $monthNumber = null;

        // 2. Cek apakah bulan adalah angka atau teks
        if (is_numeric($cleanedMonth) && $cleanedMonth >= 1 && $cleanedMonth <= 12) {
            $monthNumber = str_pad($cleanedMonth, 2, '0', STR_PAD_LEFT);
        } else {
            $months = [
                'januari' => '01', 'februari' => '02', 'maret' => '03', 'april' => '04', 'mei' => '05', 'juni' => '06',
                'juli' => '07', 'agustus' => '08', 'september' => '09', 'oktober' => '10', 'november' => '11', 'desember' => '12',
                'jan' => '01', 'feb' => '02', 'mar' => '03', 'apr' => '04', 'jun' => '06', 'jul' => '07', 'agu' => '08', 'sep' => '09', 'okt' => '10', 'nov' => '11', 'des' => '12',
            ];
            $monthNumber = $months[$cleanedMonth] ?? null;
        }

        if (!$monthNumber) {
            return null;
        }

        // 3. Membuat objek tanggal yang valid
        try {
            // Memvalidasi tanggal (misal: tidak ada tanggal 31 Februari)
            if (!checkdate($monthNumber, $cleanedDay, $cleanedYear)) {
                return null;
            }
            return Carbon::createFromFormat('Y-m-d', "{$cleanedYear}-{$monthNumber}-{$cleanedDay}")->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function uniqueBy()
    {
        return 'code_asset';
    }
}
