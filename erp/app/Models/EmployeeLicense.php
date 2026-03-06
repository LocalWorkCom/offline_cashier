<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class EmployeeLicense extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('employee-license');
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
        'employee_id',
        'has_license',
        'license_country',
        'license_expiry_date',
        'license_copy',
        'has_kuwaiti_license',
        'kuwaiti_license_copy',
        'kuwaiti_license_expiry_date',
        'has_egyptian_license',
        'egyptian_license_copy',
        'egyptian_license_expiry_date',
    ];
 protected $casts = [
        'has_license' => 'boolean',
        'has_kuwaiti_license' => 'boolean',
        'has_egyptian_license' => 'boolean',
    ];
    // Relationship to Employee (assumes Employee model exists)
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
