<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class EmployeeOpeningBalance extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('employee-opening-balance');
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
        'cashier_machine_id',
        'employee_schedule_id',
        'open_cash',
        'open_visa',
        'close_cash',
        'close_visa',
        'deficit_cash',
        'deficit_visa',
        'deficit_cash_close',
        'deficit_visa_close',
        'real_cash',
        'real_visa',
        'balance_after_sent_to_safe',
        'balance_after_sent_to_safe_visa',
        'type',
        'time',
        'date',
        'sent_to_safe',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    protected $appends = ['total_opening', 'total_closing', 'total_realing', 'total_deficiting', 'type_name', 'branch_name'];

    protected $hidden = ['created_by', 'modified_by', 'deleted_by', 'deleted_at', 'updated_at', 'created_at'];

    protected $casts = [
        'balance_after_sent_to_safe' => 'decimal:2', // or 'string'
        'balance_after_sent_to_safe_visa' => 'decimal:2', // or 'string'
    ];

    public function getTotalOpeningAttribute($value){
        return $total_opening = ($this->open_cash + $this->open_visa);
    }

    public function getTotalClosingAttribute($value){
        return $total_closing = ($this->close_cash + $this->close_visa);
    }

    public function getTotalRealingAttribute($value){
        return $total_realing = ($this->real_cash + $this->real_visa);
    }

    public function getTotalDeficitingAttribute($value){
        return $total_deficiting = ($this->deficit_cash + $this->deficit_visa);
    }

    public function getTypeNameAttribute($value){
        $lang = request()->header('lang', 'ar');
        if($this->type == 1){
            return $lang == "en" ? "Open" : "فتح دوريه";
        }else{
            return $lang == "en" ? "Close" : "غلق دوريه";
        }
    }

    public function getBranchNameAttribute($value){
        return $this->cashierMachines?->branches->name ?? null;
    }

    public function branches()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function employees()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function employeeSchedules()
    {
        return $this->belongsTo(EmployeeSchedule::class, 'employee_schedule_id', 'id');
    }

    public function cashierMachines()
    {
        return $this->belongsTo(CashierMachine::class, 'cashier_machine_id', 'id');
    }

}
