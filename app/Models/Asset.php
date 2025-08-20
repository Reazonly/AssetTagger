<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;



class Asset extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
      protected $guarded = []; // Memperbolehkan semua field diisi kecuali ID

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'specifications' => 'array', // Otomatis cast kolom JSON ke array
        'tanggal_pembelian' => 'date',
    ];

    /**
     * Mendapatkan kategori dari aset.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Mendapatkan sub-kategori dari aset.
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    /**
     * Mendapatkan perusahaan pemilik aset.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Mendapatkan pengguna aset saat ini.
     */
    public function assetUser(): BelongsTo
{
    return $this->belongsTo(AssetUser::class);
}

    /**
     * Mendapatkan seluruh riwayat pengguna aset.
     */
    public function history(): HasMany
    {
        return $this->hasMany(AssetHistory::class)->orderBy('tanggal_mulai', 'desc');
    }
}