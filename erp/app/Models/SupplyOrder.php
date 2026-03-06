<?php

// app/Models/SupplyOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplyOrder extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'supply_orders';

    protected $fillable = [
        'order_number',
        'from_store_id',
        'to_store_id',
        'type',
        'employee_id',
        'status',
        'supply_reason_id',
        'reject_reason_id',
        'created_by',
        'created_by_type',
        'updated_by',
        'updated_by_type',
        'deleted_by',
        'deleted_by_type'
    ];
    
    public function reason()
    {
        return $this->belongsTo(SupplyOrderReason::class, 'supply_reason_id');
    }

    public function items()
    {
        return $this->hasMany(SupplyOrderItem::class);
    }

    public function history()
    {
        return $this->hasMany(SupplyOrderHistory::class);
    }

    public function fromStore()
    {
        return $this->belongsTo(Store::class, 'from_store_id');
    }

    public function toStore()
    {
        return $this->belongsTo(Store::class, 'to_store_id');
    }
    public function rejectReason()
    {
        return $this->belongsTo(RejectReason::class);
    }
}
