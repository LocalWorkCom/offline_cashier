<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class LeaveNational extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('leave-national');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $fillable = ['country_id', 'country_code', 'leave_type_id', 'name_ar', 'name_en', 'date', 'current_date', 'holiday_date', 'status', 'created_by', 'created_by_type' ];

    protected $appends = ['name'];

    protected $hidden = ['name_ar', 'name_en', 'created_by_type', 'modified_by_type', 'deleted_by_type', 'created_by', 'modified_by', 'deleted_by', 'deleted_at', 'updated_at', 'created_at', 'status', 'country_code', 'leave_type_id', 'country_id', 'date'];

    public function getNameAttribute($value)
    {
        return Request()->header('lang') == "en" ? $this->name_en : $this->name_ar;
    }

    public function countries()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function leaveTypes()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
}
