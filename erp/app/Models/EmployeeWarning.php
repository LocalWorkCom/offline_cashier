<?php

namespace App\Models;

use Google\Service\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class EmployeeWarning extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('employee-warning');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $table = 'employee_warnings';

    protected $fillable = [
        'employee_id',
        'hr_manager_id',
        'description',
        'issue_date',
        'consequences',
        'action_plan',
        'hr_signature',
        'approval_status',
        'document_path',
        'acknowledgment_status',
        'acknowledged_at',
        'viewed_at',
        'hr_confirmed_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'acknowledged_at' => 'datetime',
        'hr_confirmed_at' => 'datetime',
        'viewed_at' => 'datetime',
        'approval_status' => 'string',
        'acknowledgment_status' => 'string',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function hrManager()
    {
        return $this->belongsTo(Employee::class, 'hr_manager_id');
    }
    public function canBeAcknowledged()
    {
        return $this->acknowledgment_status === 'pending';
    }

    public function canBeConfirmedByHr()
    {
        return in_array($this->acknowledgment_status, ['pending', 'refused']);
    }
}
