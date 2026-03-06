<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class EmployeeBankingInfo extends Model
{
    use LogsActivity;

    protected $table = 'employee_banking_info';

    protected $fillable = [
        'employee_id',
        'bank_name_id',
        'bank_account_number',
        'bank_iban',
        'is_payroll_account',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('employee-banking-info');
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    /**
     * 🔗 Belongs to BankName
     */
    public function bankName()
    {
        return $this->belongsTo(BankName::class, 'bank_name_id');
    }

    /**
     * 🔗 Belongs to Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
