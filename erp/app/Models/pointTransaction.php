<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pointTransaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id', 'order_id', 'type', 'points', 'amount', 'transaction_date'
    ];

    /**
     * Get the customer (user) associated with the point transaction.
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the order associated with the point transaction.
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
