<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class OrderRequestSplitItem  extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('order_requests');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
   protected $table = 'order_request_split_items';

    protected $fillable = [
        'order_request_id',
        'order_item_id',
        'from_order_id',
        'to_order_id',
        'quantity',
    ];
    public function orderRequest()
    {
        return $this->belongsTo(OrderRequest::class, 'order_request_id');
    }

    // The original order detail (item) being split
    public function orderItem()
    {
        return $this->belongsTo(OrderDetail::class, 'order_item_id');
    }

    // Original order
    public function fromOrder()
    {
        return $this->belongsTo(Order::class, 'from_order_id');
    }

    // Target order (the new split order)
    public function toOrder()
    {
        return $this->belongsTo(Order::class, 'to_order_id');
    }
}