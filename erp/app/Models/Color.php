<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Color extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['name', 'name_site'];

    protected $hidden = ['name_ar', 'name_en', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'modified_by', 'deleted_by', 'created_type', 'deleted_type', 'modified_type'];

    public function getNameAttribute()
    {
        return Request()->header('lang') == "en" ? $this->name_en : $this->name_ar;
    }

    public function getNameSiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_ar;
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('color_id');
    }
    public function productColors()
    {
        return $this->hasMany(ProductColor::class);
    }
}
