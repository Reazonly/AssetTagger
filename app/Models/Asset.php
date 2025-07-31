<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Import untuk SoftDeletes

class Asset extends Model
{
    // Menggunakan HasFactory dan SoftDeletes
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     * Baris ini akan memastikan 'tanggal_pembelian' selalu diperlakukan
     * sebagai objek tanggal (Carbon), yang memperbaiki masalah format.
     *
     * @var array
     */
    protected $casts = [
        'tanggal_pembelian' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function history()
    {
        // Menambahkan orderBy untuk mengurutkan riwayat dari yang terbaru
        return $this->hasMany(AssetHistory::class)->orderBy('tanggal_mulai', 'desc');
    }
}
