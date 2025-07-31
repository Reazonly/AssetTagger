<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    use HasFactory, SoftDeletes;

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
        return $this->hasMany(AssetHistory::class)->orderBy('tanggal_mulai', 'desc');
    }
}
