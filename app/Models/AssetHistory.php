<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetHistory extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the user associated with the history record.
     *
     * DIPERBARUI: Menambahkan withDefault() untuk mencegah error jika user tidak ditemukan.
     * Ini akan secara otomatis memberikan nilai default jika user_id di riwayat
     * merujuk ke pengguna yang sudah tidak ada lagi.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault([
            'nama_pengguna' => 'Pengguna Dihapus',
            'jabatan' => 'N/A',
            'departemen' => 'N/A',
        ]);
    }
}
