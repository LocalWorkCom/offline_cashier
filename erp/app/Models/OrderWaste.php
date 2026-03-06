<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class OrderWaste extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('order-waste');
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
    public function logs()
    {
        return $this->hasMany(OrderWasteLog::class, 'order_waste_id');
    }
}
