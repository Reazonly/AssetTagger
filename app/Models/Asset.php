<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;


class Asset extends Model
{
  

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
      protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'specifications' => 'array',
        'tanggal_pembelian' => 'date',
    ];


    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assetUser(): BelongsTo
    {
        return $this->belongsTo(AssetUser::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(AssetHistory::class)->orderBy('tanggal_mulai', 'desc');
    }

    public static function generateNextCode(Company $company, Category $category, string $itemName, ?string $merk, ?string $tipe, ?string $nomorBast): string
    {
        $companyCode = $company->code ?? 'N/A';
        $categoryCode = $category->code ?? 'N/A';

        $sourceForCode = '';
        $length = 5; 

        if (!empty($merk)) {
            $sourceForCode = $merk;
            $length = 5;
        } elseif (!empty($tipe)) {
            $sourceForCode = $tipe;
            $length = 5;
        } elseif (!empty($nomorBast)) {
            $sourceForCode = $nomorBast;
            $length = 3; 
        } else {
            $sourceForCode = $itemName; 
            $length = 5;
        }
        
        $cleanSource = preg_replace('/[^a-zA-Z0-9]/', '', $sourceForCode);
        $middlePart = strtoupper(substr($cleanSource, 0, $length));
       

        $prefix = "{$companyCode}/{$categoryCode}/{$middlePart}/";

        
        $existingNumbers = DB::table('assets')
            ->whereNotNull('code_asset')
            ->pluck('code_asset')
            ->map(function ($code) {
                return (int) substr($code, strrpos($code, '/') + 1);
            })
            ->sort()
            ->values();

        $nextNumber = 1;
        foreach ($existingNumbers as $number) {
            if ($number == $nextNumber) {
                $nextNumber++;
            } else {
                break; 
            }
        }

        $paddedNumber = str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        return $prefix . $paddedNumber;
    }
}
