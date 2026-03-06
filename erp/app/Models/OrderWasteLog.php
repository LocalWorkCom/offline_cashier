<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderWasteLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_waste_id',
        'user_id',
        'action',
        'order_id',
        'invoice_id',
        'return_invoice_request_id',
        'order_detail_id',
        'order_addon_id',
        'original_quantity',
        'waste_quantity',
        'waste_reason_id',
        'type',
        'flag',
        'reused',
        'note',
        'changed_fields',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    protected $hidden = [
        'created_by',
        'modified_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function orderWaste()
    {
        return $this->belongsTo(OrderWaste::class, 'order_waste_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class, 'order_detail_id');
    }
    public function orderAddon()
    {
        return $this->belongsTo(OrderAddon::class, 'order_addon_id');
    }
    public function wasteReason()
    {
        return $this->belongsTo(WasteReason::class, 'waste_reason_id');
    }
}
