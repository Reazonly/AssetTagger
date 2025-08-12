<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubCategory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // --- TAMBAHKAN BLOK INI ---
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'spec_fields' => 'array', // Otomatis cast kolom JSON ke array
    ];
    // --- AKHIR PENAMBAHAN ---

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
