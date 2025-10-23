<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // <-- TAMBAHKAN INI

class Company extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    // --- TAMBAHKAN FUNGSI INI ---
    /**
     * Pengguna yang memiliki hak akses ke perusahaan ini.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user');
    }
    // --- AKHIR PENAMBAHAN ---
}