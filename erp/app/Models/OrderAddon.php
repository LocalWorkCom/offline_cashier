<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class OrderAddon extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('order-addon');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    // Table associated with the model
    protected $table = 'order_addons';

    // The attributes that are mass assignable
    protected $fillable = [
        'price',
        'quantity',
        'order_id',
        'order_details_id',
        'recipe_addon_id',
        'price_before_tax',
        'price_before_coupon',
        'in_request_return',
        'price_after_tax',
        'dish_addon_id',
        'tax_value',
        'status',
        'created_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'modify_by',
        'deleted_at',
        'service_fees'
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'modify_by',
        'deleted_at',


    ];

    // Define relationships
    public function Order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function Addon()
    {
        return $this->belongsTo(DishAddon::class, 'dish_addon_id');
    }

    public function orderDetails()
    {
        return $this->belongsTo(OrderDetail::class, 'order_details_id');
    }
    // In OrderAddon model
    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class, 'order_details_id'); // Note the 's' in 'order_details_id'
    }
}
