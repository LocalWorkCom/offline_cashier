<?php

namespace App\Models;

use App\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class Violation extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasAuditTrail;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('violation');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $fillable = ['name', 'max_repeat', 'within_period', 'created_by', 'modified_by', 'deleted_by'];

    //  One violation has many violation-penalty mappings
    public function violationPenalties()
    {
        return $this->hasMany(ViolationPenalty::class, 'violation_id');
    }

    // ✅ One violation can be applied to many employees
    public function employeeViolations()
    {
        return $this->hasMany(EmployeeViolation::class, 'violation_penalty_id');
    }


}
