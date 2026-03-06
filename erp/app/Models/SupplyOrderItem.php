<?php

// app/Models/SupplyOrderItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplyOrderItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['supply_order_id', 'product_brand_id', 'quantity', 'unit_id', 'notes',  'created_by', 'updated_by', 'created_by_type', 'updated_by_type', 'deleted_by', 'deleted_by_type'];

    public function supplyOrder()
    {
        return $this->belongsTo(SupplyOrder::class);
    }
    public function productBrand()
    {
        return $this->belongsTo(ProductBrand::class, 'product_brand_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function createdBy()
    {
        return $this->morphTo(__FUNCTION__, 'created_by_type', 'created_by');
    }

    public function updatedBy()
    {
        return $this->morphTo(__FUNCTION__, 'updated_by_type', 'updated_by');
    }
    public function deletedBy()
    {
        return $this->morphTo(__FUNCTION__, 'deleted_by_type', 'deleted_by');
    }
}
