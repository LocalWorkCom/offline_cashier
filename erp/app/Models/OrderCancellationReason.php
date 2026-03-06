<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class OrderCancellationReason extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('order-cancellation-reason');
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
        'reason_ar',
        'reason_en',
        'type',
        'created_by',
        'created_by_type',
        'updated_by',
        'updated_by_type',
        'deleted_by',
        'deleted_by_type'
    ];
    protected $hidden=['created_at','updated_at','deleted_at'];
    protected $appends = ['reason'];

    protected $casts = [
        'type' => 'array',
    ];

    protected $attributes = [
        'type' => '[]', // Default empty array
    ];

    // Add mutator to ensure type is never null
    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = json_encode($value ?? []);
    }
    public function getReasonAttribute()
    {
        return request()->header('lang', 'ar') === 'ar' ? $this->reason_ar : $this->reason_en;
    }
}
