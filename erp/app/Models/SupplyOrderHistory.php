<?php
// app/Models/SupplyOrderItem.php
// app/Models/SupplyOrderHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplyOrderHistory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['supply_order_id', 'action', 'details', 'created_by', 'updated_by', 'created_by_type', 'updated_by_type', 'deleted_by_by', 'deleted_by_type'];

    public function supplyOrder()
    {
        return $this->belongsTo(SupplyOrder::class);
    }
}
