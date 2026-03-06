<?php

namespace App\Models;

use App\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class EmployeeViolation extends Model
{
    use SoftDeletes, LogsActivity, HasAuditTrail;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('employee-violation');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $fillable = ['violation_penalty_id', 'employee_id', 'date', 'created_by', 'modified_by', 'deleted_by'];

    public function violationPenalty()
    {
        return $this->belongsTo(ViolationPenalty::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

}
