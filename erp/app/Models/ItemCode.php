<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemCode extends Model
{
    use HasFactory;
    protected $table = 'item_codes';

    protected $fillable = [
        'codeName',
        'codeNameAr',
        'description',
        'description_ar',
        'codeType',
        'parentCode',
        'itemCode',
        'activeFrom',
        'activeTo',
        'requestReason',
    ];

    protected $appends = ['name', 'localized_description'];

    /**
     * Accessor for 'name' based on language header
     */
    public function getNameAttribute()
    {
        $lang = request()->header('lang', app()->getLocale());
        return $lang === 'en' ? $this->codeName : $this->codeNameAr;
    }

    /**
     * Accessor for 'localized_description' based on language
     */
    public function getLocalizedDescriptionAttribute()
    {
        $lang = request()->header('lang', app()->getLocale());
        return $lang === 'en' ? $this->description : $this->description_ar;
    }

    public function dishes()
    {
        return $this->hasMany(Dish::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
