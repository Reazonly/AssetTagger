<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;

// Mengubah dari ToModel menjadi ToCollection agar bisa melakukan aksi tambahan
class AssetsImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    /**
     * Memproses koleksi baris dari file Excel.
     *
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            // Lewati baris jika kode aset kosong
            if (empty($row['code_asset'])) {
                continue;
            }

            // 1. Cari atau buat pengguna (user) dari data Excel
            $user = null;
            if (!empty($row['nama_2'])) {
                $user = User::firstOrCreate(
                    ['nama_pengguna' => trim($row['nama_2'])],
                    ['jabatan' => trim($row['jabatan_2'] ?? null), 'departemen' => trim($row['departemen_2'] ?? null)]
                );
            }
            $newUserId = $user ? $user->id : null;

            // 2. Buat atau perbarui data aset (seperti WithUpserts)
            $asset = Asset::updateOrCreate(
                ['code_asset' => trim($row['code_asset'])],
                [
                    'nama_barang'       => trim($row['nama_item']),
                    'merk_type'         => trim($row['type'] ?? null),
                    'serial_number'     => trim($row['serial_number'] ?? null),
                    'user_id'           => $newUserId, // Tetapkan pengguna baru
                    'processor'         => trim($row['processor'] ?? null),
                    'memory_ram'        => trim($row['memory_ram'] ?? null),
                    'hdd_ssd'           => trim($row['hdd_ssd'] ?? $row['storage'] ?? $row['hddssd'] ?? null),
                    'graphics'          => trim($row['graphics'] ?? null),
                    'lcd'               => trim($row['lcd'] ?? null),
                    'tanggal_pembelian' => $this->parseDate($row['tgl'] ?? null, $row['bulan'] ?? null, $row['tahun'] ?? null),
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
                ]
            );

            // 3. LOGIKA PENCATATAN RIWAYAT PENGGUNA
            $latestHistory = $asset->history()->first();

            // Hanya buat riwayat jika:
            // - Belum ada riwayat sama sekali, ATAU
            // - Pengguna di riwayat terakhir berbeda dengan pengguna dari Excel
            if (!$latestHistory || $latestHistory->user_id != $newUserId) {
                
                // Tutup semua riwayat lama yang mungkin masih aktif untuk aset ini
                $asset->history()->whereNull('tanggal_selesai')->update(['tanggal_selesai' => now()]);

                // Jika ada pengguna baru di file Excel, buatkan riwayat baru untuknya
                if ($newUserId) {
                    $asset->history()->create([
                        'user_id'       => $newUserId,
                        'tanggal_mulai' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Fungsi yang sangat kuat untuk mengubah data tanggal terpisah menjadi format standar.
     */
    private function parseDate($day, $month, $year)
    {
        if (is_null($day) || is_null($month) || is_null($year)) {
            return null;
        }
        $cleanedDay = (int) preg_replace('/[^\d]/', '', $day);
        $cleanedMonth = strtolower(trim($month));
        $cleanedYear = (int) preg_replace('/[^\d]/', '', $year);
        if (empty($cleanedDay) || empty($cleanedMonth) || empty($cleanedYear)) {
            return null;
        }
        $monthNumber = null;
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
        try {
            if (!checkdate($monthNumber, $cleanedDay, $cleanedYear)) {
                return null;
            }
            return Carbon::createFromFormat('Y-m-d', "{$cleanedYear}-{$monthNumber}-{$cleanedDay}")->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Menentukan ukuran batch untuk membaca file Excel.
     * Ini membantu menghemat memori saat mengimpor file besar.
     */
    public function chunkSize(): int
    {
        return 100;
    }
}
