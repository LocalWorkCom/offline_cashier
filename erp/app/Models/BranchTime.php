<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class BranchTime extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('branch-time');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $appends = ['opening_hour_convert', 'closing_hour_convert'];

    protected $fillable = [
        'branch_id', 
        'day', 
        'opening_hour', 
        'closing_hour', 
        'cross_day', 
        'created_by', 
        'modified_by',
        'deleted_by',
        'is_active'
    ];

    protected $hidden = [
        'created_by',
        'modified_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function getOpeningHourConvertAttribute()
    {
        return date('h:i A', strtotime($this->opening_hour));
    }
    public function getClosingHourConvertAttribute()
    {
        return date('h:i A', strtotime($this->closing_hour));
    }
}
