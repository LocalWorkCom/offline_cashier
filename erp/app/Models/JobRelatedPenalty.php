<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class JobRelatedPenalty extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('job-type');
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
        'curr_possition_id',
        'new_possition_id',
        'curr_department_id',
        'new_department_id',
        'curr_branch_id',
        'new_branch_id',
        'privilege_type_id',
        'effective_date',
        'reason',
        'restricted_system',
        'status',
        'type',
        'created_by',
        'created_by_type',
        'modified_by',
        'modified_by_type',
        'deleted_by',
        'deleted_by_type',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'created_by_type',
        'modified_by',
        'modified_by_type',
        'deleted_by',
        'deleted_by_type'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function currPosition()
    {
        return $this->belongsTo(Position::class, 'curr_possition_id');
    }

    public function newPosition()
    {
        return $this->belongsTo(Position::class, 'new_possition_id');
    }

    public function currDepartment()
    {
        return $this->belongsTo(Department::class, 'curr_department_id');
    }

    public function newDepartment()
    {
        return $this->belongsTo(Department::class, 'new_department_id');
    }

    public function currBranch()
    {
        return $this->belongsTo(Branch::class, 'curr_branch_id');
    }

    public function newBranch()
    {
        return $this->belongsTo(Branch::class, 'new_branch_id');
    }

    public function privilegeType()
    {
        return $this->belongsTo(PrivilegeType::class, 'privilege_type_id');
    }

    public function documents()
    {
        return $this->hasMany(JobRelatedPenaltyDocument::class, 'job_related_id');
    }
}
