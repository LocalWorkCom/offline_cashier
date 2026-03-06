<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderRefund extends Model
{
    use HasFactory, SoftDeletes;

    // Table associated with the model
    protected $table = 'order_refunds';

    // The attributes that are mass assignable
    protected $fillable = [
        'reason',
        'order_detail_id',
        'invoice_number'
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'modify_by',
        'deleted_at'
    ];

    // The attributes that should be cast to native types
    // protected $casts = [
    //     'is_valid' => 'boolean',
    // ];

    // Define relationships

    public function OrderDetail()
    {
        return $this->belongsTo(OrderDetail::class, 'order_detail_id');
    }

    public function orders()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
