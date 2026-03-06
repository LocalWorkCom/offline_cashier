<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'unit_id',
        'price',
        'quantity',  // Path for brand logo
       
    ];

    protected $hidden = [
        'created_by',
        'modified_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function products()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function orders()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function unites()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
