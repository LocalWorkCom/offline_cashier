<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;
class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('leave-request');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $fillable = ['employee_id', 'leave_type_id', 'date', 'from', 'to', 'leave_count', 'resone', 'status', 'position_id', 'request_num', 'created_by' ];

    protected $appends = ['approve', 'approve_by'];

    protected $hidden = ['created_by', 'modified_by', 'deleted_by', 'deleted_at', 'updated_at', 'created_at'];

    public function getApproveAttribute($value)
    {
        $employee = Auth::guard('employee')->user();

        if (!$employee) {
            return false;
        }

        $employeePositionId = (int) $employee->position_id;

        $setting = $this->leaveSettings; // single LeaveSetting model

        if (!$setting) {
            return false; // no related leave setting
        }

        // Check if this employee's position is in any of the approver arrays
        $isApproved = $setting->leaveSettingPositions->contains(function ($position) use ($employeePositionId) {
            $approvers = is_array($position->higher_position_approve)
                ? $position->higher_position_approve
                : json_decode($position->higher_position_approve, true);

            return is_array($approvers) && in_array($employeePositionId, $approvers);
        });

        return $isApproved;
    }

    public function getApproveByAttribute($value)
    {
        $employee = Auth::guard('employee')->user();

        if (!$employee) {
            return false;
        }

        $agree = $this->leaveRequestAgreements->first();

        if (!$agree) {
            return null;
        }
        $name = optional($agree->employee)->full_name;
        unset($this->leaveRequestAgreements);
        return $name;
    }

    public function employees()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }
    public function leaveTypes()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function leaveSettings()
    {
        return $this->belongsTo(LeaveSetting::class, 'leave_type_id');
    }

    public function leaveRequestAgreements()
    {
        return $this->hasMany(LeaveRequestAgreement::class, 'leave_request_id', 'id');
    }

    public function agreementBys()
    {
        return $this->belongsTo(User::class, 'agreement_by');
    }
}
