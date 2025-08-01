<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\User;
use App\Models\Category; // Import the Category model
use App\Models\Company;  // Import the Company model
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;

class AssetsImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            // Skip row if essential data like nama_item is missing
            if (empty($row['nama_item'])) {
                continue;
            }

            // 1. Find or create the Category
            $category = null;
            if (!empty($row['kategori'])) {
                $category = Category::firstOrCreate(
                    ['name' => trim($row['kategori'])]
                );
            }

            // 2. Find or create the User
            $user = null;
            if (!empty($row['nama_pengguna'])) {
                $user = User::firstOrCreate(
                    ['nama_pengguna' => trim($row['nama_pengguna'])],
                    ['jabatan' => trim($row['jabatan'] ?? null), 'departemen' => trim($row['departemen'] ?? null)]
                );
            }
            
            // 3. Find the Company (assuming company code exists in Excel)
            $company = null;
            if (!empty($row['kode_perusahaan'])) {
                $company = Company::where('code', trim($row['kode_perusahaan']))->first();
            }

            // Prepare data for asset creation/update
            $assetData = [
                'nama_barang'       => trim($row['nama_item']),
                'category_id'       => $category ? $category->id : null,
                'user_id'           => $user ? $user->id : null,
                'company_id'        => $company ? $company->id : null,
                'merk'              => trim($row['merk'] ?? null),
                'tipe'              => trim($row['tipe'] ?? null),
                'serial_number'     => trim($row['serial_number'] ?? null),
                'processor'         => trim($row['processor'] ?? null),
                'memory_ram'        => trim($row['memory_ram'] ?? null),
                'hdd_ssd'           => trim($row['hdd_ssd'] ?? $row['storage'] ?? null),
                'graphics'          => trim($row['graphics'] ?? null),
                'lcd'               => trim($row['lcd'] ?? null),
                'tanggal_pembelian' => $this->parseDate($row['tanggal_pembelian'] ?? null),
                'thn_pembelian'     => $this->parseYear($row['tanggal_pembelian'] ?? null),
                'po_number'         => trim($row['po_number'] ?? null),
                'harga_total'       => is_numeric($row['harga_total'] ?? null) ? $row['harga_total'] : null,
                'code_aktiva'       => trim($row['code_aktiva'] ?? null),
                'kondisi'           => trim($row['kondisi'] ?? 'BAIK'),
                'lokasi'            => trim($row['lokasi'] ?? null),
                'jumlah'            => is_numeric($row['jumlah'] ?? null) ? $row['jumlah'] : 1,
                'satuan'            => trim($row['satuan'] ?? 'UNIT'),
                'nomor'             => trim($row['nomor_bast'] ?? null),
                'include_items'     => trim($row['include_items'] ?? null),
                'peruntukan'        => trim($row['peruntukan'] ?? null),
                'keterangan'        => trim($row['keterangan'] ?? null),
            ];
            
            // Use a unique identifier for updateOrCreate, e.g., serial_number if available and unique
            // If not, we might need a different strategy. For now, let's assume S/N is the key.
            $uniqueIdentifier = ['serial_number' => $assetData['serial_number']];

            // Only use S/N for lookup if it's not null
            if (is_null($assetData['serial_number'])) {
                // If S/N is null, we can't reliably update, so we create a new asset.
                // A 'code_asset' will be generated upon creation.
                $asset = Asset::create($assetData);
            } else {
                // If S/N exists, try to update or create
                $asset = Asset::updateOrCreate($uniqueIdentifier, $assetData);
            }


            // 4. Update Asset Code if it was newly created
            if ($asset->wasRecentlyCreated) {
                $asset->code_asset = $this->generateAssetCode($asset);
                $asset->save();
            }
            
            // 5. Handle user history
            $this->updateUserHistory($asset, $user ? $user->id : null);
        }
    }

    /**
     * Generate asset code based on the asset's data.
     */
    private function generateAssetCode(Asset $asset): string
    {
        $namaBarang = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $asset->nama_barang), 0, 3));
        
        $category = $asset->category;
        $company = $asset->company;

        $merkOrTipe = optional($category)->requires_merk ? $asset->merk : $asset->tipe;
        $merkOrTipeCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $merkOrTipe), 0, 3));

        $companyCode = optional($company)->code ?? 'N/A';
        $paddedId = str_pad($asset->id, 3, '0', STR_PAD_LEFT);

        return "{$namaBarang}/{$merkOrTipeCode}/{$companyCode}/{$paddedId}";
    }

    /**
     * Update the user history for the asset.
     */
    private function updateUserHistory(Asset $asset, ?int $newUserId): void
    {
        $latestHistory = $asset->history()->latest()->first();

        if (!$latestHistory || $latestHistory->user_id != $newUserId) {
            // End date for any previous history record for this asset
            $asset->history()->whereNull('tanggal_selesai')->update(['tanggal_selesai' => now()]);

            // Create a new history record if there's a new user
            if ($newUserId) {
                $asset->history()->create([
                    'user_id'       => $newUserId,
                    'tanggal_mulai' => now(),
                ]);
            }
        }
    }

    /**
     * Robust date parser.
     */
    private function parseDate($dateValue)
    {
        if (empty($dateValue)) {
            return null;
        }
        try {
            // Handles both Excel's numeric date and string dates like 'Y-m-d' or 'd-m-Y'
            if (is_numeric($dateValue)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue))->toDateString();
            }
            return Carbon::parse($dateValue)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse year from a date value.
     */
    private function parseYear($dateValue)
    {
        $date = $this->parseDate($dateValue);
        return $date ? Carbon::parse($date)->format('Y') : null;
    }

    /**
     * Define chunk size for memory-efficient reading.
     */
    public function chunkSize(): int
    {
        return 100;
    }
}
