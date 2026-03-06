<?php

namespace App\Models;

use App\Traits\HasAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class ViolationPenalty extends Model
{
    use SoftDeletes, LogsActivity, HasAuditTrail;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('violation-penalty');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $fillable = ['violation_id', 'penalty_id', 'order_penalty', 'created_by', 'modified_by', 'deleted_by'];

    public function violation()
    {
        return $this->belongsTo(Violation::class);
    }

    public function penalty()
    {
        return $this->belongsTo(PenaltyReason::class);
    }

    public function employeeViolations()
    {
        return $this->hasMany(EmployeeViolation::class);
    }
    public function violationPenalties()
    {
        return $this->hasMany(ViolationPenalty::class);
    }
}
