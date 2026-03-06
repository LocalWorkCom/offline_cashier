<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class EmployeeAdditionalInfo extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('employee-additional-info');
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
        'additional_description',
        'referral_source',
        'languages_spoken',
        'visa_information',
        'is_residency_transferable',
        'tasks_and_instructions',
        'criminal_record_file',
        'drug_test_report_file',
    ];
    protected $casts = [
        'is_residency_transferable' => 'boolean',
    ];

    /**
     * Relationship: Each additional info belongs to an employee.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
