<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class TerminationOfService extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    // Laravel 8+ automatically handles deleted_at with SoftDeletes, no need for $dates
    protected $fillable = [
        'employee_id',
        'reason',
        'start_date',
        'end_date',
        'last_day',
        'severance_package',
        'approval_workflow',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'last_day'   => 'date',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('termination-of-service');
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    // Relations
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function documents()
    {
        return $this->hasMany(TerminationDocument::class, 'termination_id');
    }
}
