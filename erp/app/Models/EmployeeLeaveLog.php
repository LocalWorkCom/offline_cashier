<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class EmployeeLeaveLog extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('employee-leave-log');
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $fillable = ['employee_id', 'position_id', 'leave_request_id', 'leave_type_id', 'date', 'from', 'to', 'leave_count', 'resone', 'day_count', 'day_paid', 'day_unpaid', 'deduction_value', 'deduction_days', 'created_by' ];

    public function employees()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function leaveTypes()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function positions()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function leaveRequests()
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_request_id');
    }
}
