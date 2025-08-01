<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_pembelian' => 'date',
    ];

    /**
     * Get the user that owns the asset.
     * DIPERBARUI: Menambahkan withDefault() untuk mencegah error jika user tidak ditemukan.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault([
            'nama_pengguna' => 'Tidak Ada Pengguna',
            'jabatan' => 'N/A',
            'departemen' => 'N/A',
        ]);
    }

    /**
     * Get the history records for the asset.
     */
    public function history()
    {
        return $this->hasMany(AssetHistory::class)->orderBy('tanggal_mulai', 'desc');
    }
}
