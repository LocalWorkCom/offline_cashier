<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KitchenLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'order_details_id',
        'dish_id',
        'dish_size_id',
        'offer_id',
        'date',
        'time',
        'quantity',
        'status',
        'branch_id',
        'table_id',
        'employee_id',
        'store_id',
        'order_addone_id',
        'dish_addone_id',
        'order_type',
        'created_by',
        'modified_by',
        'deleted_by',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $with = ['order', 'dish', 'branch'];
    
    // Relations

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class, 'order_details_id');
    }

    public function dish()
    {
        return $this->belongsTo(Dish::class);
    }

    public function dishSize()
    {
        return $this->belongsTo(DishSize::class);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function orderAddone()
    {
        return $this->belongsTo(OrderAddon::class);
    }

    public function dishAddone()
    {
        return $this->belongsTo(DishAddon::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
