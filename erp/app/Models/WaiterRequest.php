<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class WaiterRequest extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('waiter-request');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $fillable = [
        'order_ids',
        'order_items_ids',
        'status',
        'reason',
        'table_id',
        'coupon_id',
        'branch_id',
        'employee_id'

    ];
    protected $casts = [
        'order_ids' => 'array', // Automatically convert JSON to array
    ];

    /**
     * Get the employee who created the request.
     */
    public function originalOrders()
{
    return $this->belongsToMany(Order::class, 'waiter_requests','original_order_details');
}

    public function tables()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }
    /**
     * Get the user related to this request.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship to the main order (for split or merge)
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_ids');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_ids');
    }

    public function waiterRequests()
    {
        return $this->hasMany(WaiterRequest::class, 'order_ids')
            ->whereRaw('JSON_CONTAINS(order_ids, CAST(id AS JSON))');
    }
}
