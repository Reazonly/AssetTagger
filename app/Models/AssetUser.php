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
    protected $guarded = [];

    
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    
}
