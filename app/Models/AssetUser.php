<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetUser extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    // PERBAIKAN DI SINI: Izinkan semua field untuk diisi.
    protected $guarded = [];

    /**
     * Mendefinisikan bahwa satu Pengguna Aset bisa memiliki banyak Aset.
     */
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    /**
     * Mendefinisikan bahwa satu Pengguna Aset dimiliki oleh satu Perusahaan.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    
}
