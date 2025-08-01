<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    /**
     * The HasFactory and SoftDeletes traits are used for model factories and soft deleting.
     */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
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
     * Note: This assumes you have an 'AssetHistory' model defined in your application.
     */
    public function history()
    {
        // Orders the history records from the newest to the oldest.
        return $this->hasMany(AssetHistory::class)->orderBy('tanggal_mulai', 'desc');
    }
}
