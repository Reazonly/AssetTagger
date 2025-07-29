<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;
    
    // Izinkan semua atribut untuk diisi secara massal, kecuali ID.
    protected $guarded = ['id'];

    public function assets()
    {
        return $this->hasMany(Asset::class, 'user_id');
    }
}