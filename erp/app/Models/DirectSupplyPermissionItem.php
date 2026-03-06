<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectSupplyPermissionItem extends Model
{
    protected $fillable = [
        'dsp_id',
        'category_id',
        'product_brand_id',
        'unit_id',
        'received_unit_id',
        'received_quantity',
        'has_added',
        'quantity',
        'notes',
        'created_by',
        'created_by_type',
        'modified_by',
        'modified_by_type',
        'deleted_by',
        'deleted_by_type'
    ];


    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id')->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo(ProductBrand::class, 'product_brand_id')->withTrashed();

    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id')->withTrashed();
    }

    public function dsp()
    {
        return $this->belongsTo(DirectSupplyPermission::class, 'dsp_id');
    }
    public function issues()
    {
        return $this->hasMany(DspItemIssue::class, 'dsp_item_id');
    }

    public function returns()
    {
        return $this->hasMany(ReturnDsp::class, 'dsp_id', 'dsp_id');
    }
}
