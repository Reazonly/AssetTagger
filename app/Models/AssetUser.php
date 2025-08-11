<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetUser extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Mendefinisikan bahwa satu Pengguna Aset bisa memiliki banyak Aset.
     */
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}