<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Mendefinisikan relasi bahwa setiap SubCategory dimiliki oleh satu Category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}