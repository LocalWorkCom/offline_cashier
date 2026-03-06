<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class OrderTracking extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('order-tracking');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $table = 'order_trackings';

    protected $fillable = [
        'order_status',
        'order_id',
        'created_by',
    ];

    const pending = 1;
    const inProgress = 2;
    const onWay = 3;
    const delivered = 4;
    const readyForPickup = 5;
    const completed = 6;
    const cancelled = 7;

    public static $statusMap = [
        self::pending => 'pending',
        self::inProgress => 'in_progress',
        self::onWay => 'on_way',
        self::delivered => 'delivered',
        self::readyForPickup => 'readyForPickup',
        self::cancelled => 'cancelled',
        self::completed => 'completed',
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'updated_at',
        'modify_by',
        'deleted_at',
    ];

    // // The attributes that should be cast to native types
    // protected $casts = [
    //     'is_valid' => 'boolean',
    // ];

    // Define relationships

    public function Order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
