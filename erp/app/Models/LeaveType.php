<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UUID;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class LeaveType extends Model
{
    // use HasFactory, SoftDeletes, LogsActivity, UUID;
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('leave-type');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $appends = ['name', 'name_site', 'details'];

    protected $hidden = ['name_ar', 'name_en', 'created_by', 'modified_by', 'deleted_by', 'deleted_at', 'updated_at', 'created_at', 'created_by_type', 'modified_by_type', 'deleted_by_type'];

    public function getNameAttribute($value){
        return Request()->header('lang') == "en" ? $this->name_en : $this->name_ar;
    }

    public function getDetailsAttribute($value){
        return Request()->header('lang') == "en" ? $this->details_en : $this->details_ar;
    }

    public function getNameSiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_ar;
    }

    public function employees_leaves()
    {
        return $this->belongsToMany(Employee::class, 'employee_leaves', 'leave_type_id', 'employee_id')
                    ->withPivot('day_count', 'day_paid', 'day_unpaid') // if you want to access extra columns
                    ->withTimestamps();
    }

    public function leaveSettings()
    {
        return $this->hasMany(LeaveSetting::class, 'leave_type_id');
    }
}
