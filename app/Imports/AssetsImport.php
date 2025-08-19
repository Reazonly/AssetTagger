<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Company;
use App\Models\AssetUser;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AssetsImport implements ToCollection, WithHeadingRow, WithValidation
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            Log::info('Processing row: ', $row->toArray());

            // 1. Cari atau buat data master
            $category = Category::firstOrCreate(['name' => $row['kategori']], ['code' => substr(Str::slug($row['kategori']), 0, 10)]);
            $subCategory = ($category && !empty($row['sub_kategori'])) ? SubCategory::firstOrCreate(['name' => $row['sub_kategori'], 'category_id' => $category->id]) : null;
            $company = !empty($row['perusahaan_pemilik_kode']) ? Company::where('code', $row['perusahaan_pemilik_kode'])->first() : null;
            
            $assetUser = null;
            if (!empty($row['pengguna_aset'])) {
                $userCompany = !empty($row['perusahaan_pengguna_kode']) ? Company::where('code', $row['perusahaan_pengguna_kode'])->first() : null;
                $assetUser = AssetUser::firstOrCreate(
                    ['nama' => $row['pengguna_aset']],
                    ['jabatan' => $row['jabatan_pengguna'] ?? null, 'departemen' => $row['departemen_pengguna'] ?? null, 'company_id' => optional($userCompany)->id]
                );
            }
            $newAssetUserId = optional($assetUser)->id;

            // 2. Siapkan data aset
            $specifications = [];
            foreach ($row as $key => $value) {
                if (!in_array($key, $this->defaultHeadings()) && !empty($value)) {
                    $specifications[$key] = $value;
                }
            }
            $tanggal_pembelian = null;
            if (!empty($row['tanggal_pembelian'])) {
                if (is_numeric($row['tanggal_pembelian'])) {
                    $tanggal_pembelian = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tanggal_pembelian']);
                } else {
                    try { $tanggal_pembelian = Carbon::parse($row['tanggal_pembelian']); } catch (\Exception $e) { $tanggal_pembelian = null; }
                }
            }

            $assetData = [
                'nama_barang' => $row['nama_barang'], 'category_id' => optional($category)->id,
                'sub_category_id' => optional($subCategory)->id, 'company_id' => optional($company)->id,
                'merk' => $row['merk'] ?? null, 'tipe' => $row['tipe'] ?? null,
                'asset_user_id' => $newAssetUserId, 'kondisi' => $row['kondisi'] ?? 'Baik',
                'lokasi' => $row['lokasi'] ?? null, 'jumlah' => $row['jumlah'] ?? 1,
                'satuan' => $row['satuan'] ?? 'Unit', 'tanggal_pembelian' => $tanggal_pembelian,
                'harga_total' => $row['harga_total_rp'] ?? null, 'po_number' => $row['nomor_po'] ?? null,
                'nomor' => $row['nomor_bast'] ?? null, 'code_aktiva' => $row['kode_aktiva'] ?? null,
                'sumber_dana' => $row['sumber_dana'] ?? null, 'include_items' => $row['item_termasuk'] ?? null,
                'peruntukan' => $row['peruntukan'] ?? null, 'keterangan' => $row['keterangan'] ?? null,
                'specifications' => $specifications,
            ];

            // 3. Cari atau buat/update aset
            $asset = null;
            if (!empty($row['kode_aset'])) {
                $asset = Asset::withTrashed()->where('code_asset', $row['kode_aset'])->first();
            } elseif (!empty($row['serial_number'])) {
                $asset = Asset::withTrashed()->where('serial_number', $row['serial_number'])->first();
            }

            if ($asset) {
                if ($asset->trashed()) { $asset->restore(); }
                $asset->update($assetData);
            } else {
                if (!empty($row['kode_aset'])) $assetData['code_asset'] = $row['kode_aset'];
                if (!empty($row['serial_number'])) $assetData['serial_number'] = $row['serial_number'];
                $asset = Asset::create($assetData);
            }
            $asset->refresh(); // Ambil data terbaru dari DB

            // --- PERBAIKAN LOGIKA HISTORY DI SINI ---
            $currentUserId = $asset->asset_user_id;
            Log::info("Asset [{$asset->code_asset}] - Final User ID: {$currentUserId}");

            // Tutup semua riwayat yang mungkin masih terbuka untuk aset ini
            $asset->history()->whereNull('tanggal_selesai')->update(['tanggal_selesai' => now()]);

            // Jika ada pengguna saat ini, buat entri riwayat baru yang aktif
            if ($currentUserId) {
                Log::info("--> Creating new active history for user ID: {$currentUserId}.");
                $asset->history()->create([
                    'asset_user_id' => $currentUserId,
                    'tanggal_mulai' => now(),
                ]);
            } else {
                Log::info("--> No current user. All histories closed.");
            }
        }
    }

    public function rules(): array
    {
        return [ '*.nama_barang' => 'required|string', '*.kategori' => 'required|string', ];
    }

    private function defaultHeadings(): array
    {
        return [
            'kode_aset', 'nama_barang', 'kategori', 'sub_kategori', 'perusahaan_pemilik_kode',
            'merk', 'tipe', 'serial_number', 'pengguna_aset', 'jabatan_pengguna',
            'departemen_pengguna', 'perusahaan_pengguna_kode', 'kondisi', 'lokasi', 'jumlah',
            'satuan', 'tanggal_pembelian', 'harga_total_rp', 'nomor_po', 'nomor_bast',
            'kode_aktiva', 'sumber_dana', 'item_termasuk', 'peruntukan', 'keterangan',
            'riwayat_pengguna',
        ];
    }
}
