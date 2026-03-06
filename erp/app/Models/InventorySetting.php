<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class InventorySetting extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('inventory-setting');
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
        'notify_before_expiration',
        'auto_purchase_order_on_low_stock',
        'notification_frequency',
        'no_of_days',
        'receive_notifications',
        'notification_recipient',
    ];
    protected $casts = [
        'notify_before_expiration' => 'boolean',
        'auto_purchase_order_on_low_stock' => 'boolean',
        'receive_notifications' => 'boolean',

    ];
}
