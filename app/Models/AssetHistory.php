<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetHistory extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     *
     */
    public function assetUser()
    {
        return $this->belongsTo(AssetUser::class, 'asset_user_id')->withDefault([
            'nama' => 'Pengguna Dihapus',
            'jabatan' => 'N/A',
            'departemen' => 'N/A',
        ]);
    }
}
