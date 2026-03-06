<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplyOrderReason extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'supply_order_reasons';


    protected $fillable = [
        'name_en',
        'name_ar',
        'created_by',
        'created_by_type',
        'updated_by',
        'updated_by_type',
        'deleted_by',
        'deleted_by_type',
    ];
    protected $appends = ['name'];
    public function supplyOrders()
    {
        return $this->hasMany(SupplyOrder::class);
    }
    public function getNameAttribute()
    {
        return Request()->header('lang') == 'en' ? $this->name_en : $this->name_ar;
    }
}
