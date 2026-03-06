<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UUID;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class LeaveSetting extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('leave-setting');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $table = 'leave_settings';
    protected $hidden = ['created_by', 'modified_by', 'deleted_by', 'deleted_at', 'updated_at', 'created_at', 'created_by_type', 'modified_by_type', 'deleted_by_type'];

    public function countries()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function leaveTypes()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
    public function positions()
    {
        return $this->hasMany(LeaveSettingPosition::class, 'leave_setting_id', 'id');
    }

    public function leaveSettingPositions()
    {
        return $this->hasMany(LeaveSettingPosition::class, 'leave_setting_id', 'id');
    }

}
