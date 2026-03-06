<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class Vehicle extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('vehicle');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $fillable = ['vehicle_type', 'license'];
    protected $appends = ['employee_id'];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at','created_by','created_type','updated_by','updated_type','deleted_by','deleted_type'];
    public function getEmployeeIdAttribute()
    {
        return $this->employee?->id;
    }
    public function type()
    {
        return $this->belongsTo(VehicleSetting::class, 'vehicle_type');
    }
    public function employee()
{
    return $this->hasOne(Employee::class, 'vehicle_id');
}
}
