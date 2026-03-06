<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class EmployeeSalaryDetail extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('employee-salary-detail');
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
        'base_salary',
        'salary_work_permit',
        // 'payment_frequency',
        // 'payment_type',
        'annual_salary',
        'currency',
        // 'is_bounce_allowance',
        'currency_code',
        'commission_amount',
        'commission_type',
        'insurance_registered',
        'insurance_subscription_amount',
        'is_allowance',
        'is_bonus',
        'is_commision'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'deleted_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
