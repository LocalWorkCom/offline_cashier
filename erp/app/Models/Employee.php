<?php

namespace App\Models;

use App\Models\PerformanceReview;
use Laravel\Passport\HasApiTokens;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use App\Models\ChefCuisineCategory;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Employee extends Authenticatable
{
    use HasFactory, LogsActivity, HasApiTokens, HasRoles;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('employee');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $guard_name = 'employee';

    protected $fillable = [
        'user_id',
        'city_id',
        'area_id',
        'country_id',
        'job_type_id',
        'employee_code',
        'first_name',
        'last_name',
        'first_name_en',
        'last_name_en',
        'email',
        'phone_number',
        'country_code',
        'gender',
        'birth_date',
        'national_id',
        'nationality_id',
        'passport_number',
        'marital_status_id',
        'blood_group',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_phone',
        'address',
        'nationality',
        'department_id',
        'position_id',
        'position_name',
        'sub_department_id',
        'supervisor_id',
        'hire_date',
        'salary',
        'employment_type',
        'status',
        'flag',
        'notes',
        'is_biometric',
        'biometric_id',
        'ethnic_background_id',
        'employee_status_id',
        'bank_name_id',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'modified_by',
        'deleted_by',
        'password',
    ];

    protected $appends = ['full_name', 'kitchen_info'];

    public function getKitchenInfoAttribute()
    {
        return getEmployeeCuisine($this->id);
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function employeeSchedules()
    {
        return $this->hasMany(EmployeeSchedule::class);
    }
    public function shiftDetails()
    {
        return $this->hasManyThrough(ShiftDetail::class, EmployeeSchedule::class);
    }

    public function employeeShiftDetails()
    {
        return $this->hasManyThrough(
            ShiftDetail::class,       // Final model
            EmployeeSchedule::class,  // Intermediate model
            'employee_id',            // Foreign key on employee_schedules referencing employees.id
            'shift_id',               // Foreign key on shift_details referencing employee_schedules.shift_id
            'id',                     // Local key on employees table
            'shift_id'                // Local key on employee_schedules table
        );
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function subDepartment()
    {
        return $this->belongsTo(Department::class, 'sub_department_id');
    }

    public function nationality()
    {
        return $this->belongsTo(Nationality::class);
    }
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function penalties()
    {
        return $this->hasMany(Penalty::class);
    }
    public function delays()
    {
        return $this->hasMany(Delay::class);
    }
    public function advances()
    {
        return $this->hasMany(Advance::class);
    }
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
    // Relationship to CashierSettings
    public function cashierSettings()
    {
        return $this->hasMany(CashierSetting::class, 'employee_id');
    }
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function chefCuisineCategories()
    {
        return $this->hasMany(ChefCuisineCategory::class, 'employee_id');
    }
    public function initiatedChats(): MorphMany
    {
        return $this->morphMany(ChatChannel::class, 'initiator');
    }

    /**
     * Get all chats where this employee is the participant
     */
    public function participantChats(): MorphMany
    {
        return $this->morphMany(ChatChannel::class, 'participant');
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    // public function machine()
    // {
    //     return $this->hasOne(EmployeeMachine::class, 'employee_id');
    // }
    public function machine()
    {
        return $this->belongsToMany(CashierMachine::class, 'employee_machines', 'employee_id', 'cashier_machine_id');
    }
    public function scheduleForDate($date)
    {
        // 1️⃣ Get the latest schedule overall
        $latestSchedule = $this->employeeSchedules()
            ->orderBy('start_date', 'desc')
            ->first();

        // 2️⃣ Check if the latest schedule is valid for the given date
        if (
            $latestSchedule &&
            $latestSchedule->start_date <= $date &&
            (is_null($latestSchedule->end_date) || $latestSchedule->end_date >= $date)
        ) {
            return $latestSchedule;
        }

        // 3️⃣ Otherwise, find a schedule valid for that date
        return $this->employeeSchedules()
            ->whereDate('start_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $date);
            })
            ->orderBy('start_date', 'desc')
            ->first();
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'cashier_id');
    }
    public function ethnicBackground()
    {
        return $this->belongsTo(EthnicBackground::class, 'ethnic_background_id');
    }
    public function bankName()
    {
        return $this->belongsTo(BankName::class, 'bank_name_id');
    }

    public function maritalStatus()
    {
        return $this->belongsTo(MaritalStatus::class, 'marital_status_id');
    }
    public function militaryStatus()
    {
        return $this->belongsTo(MilitaryServiceStatus::class, 'military_service_status_id');
    }
    public function employeeStatus()
    {
        return $this->belongsTo(EmployeeStatus::class, 'employee_status_id');
    }
    public function educations()
    {
        return $this->hasMany(EmployeeEducation::class, 'employee_id');
    }
    public function employeeBankingInfo()
    {
        return $this->hasMany(EmployeeBankingInfo::class, 'employee_id');
    }
    public function employeeContactInfo()
    {
        return $this->hasMany(EmployeeContactInfo::class, 'employee_id');
    }
    public function experience()
    {
        return $this->hasMany(EmployeeExperience::class, 'employee_id');
    }
    public function legalDocument()
    {
        return $this->hasMany(EmployeeLegalDocument::class, 'employee_id');
    }
    public function employeeSalaryDetail()
    {
        return $this->hasMany(EmployeeSalaryDetail::class, 'employee_id');
    }
    public function employeeJobTypeDetail()
    {
        return $this->belongsTo(JobTypeSetting::class, 'job_type_id');
    }
    public function employeePayrollSettings()
    {
        return $this->hasMany(EmployeePayrollSetting::class, 'employee_id');
    }
    public function license()
    {
        return $this->hasOne(EmployeeLicense::class);
    }
    public function additionalInfo()
    {
        return $this->hasOne(EmployeeAdditionalInfo::class, 'employee_id');
    }
    public function biometricTransactions()
    {
        return $this->hasMany(BiometricTransaction::class, 'emp_code', 'employee_code');
    }
    public function warnings()
    {
        return $this->hasMany(EmployeeWarning::class, 'employee_id');
    }

    public function issuedWarnings()
    {
        return $this->hasMany(EmployeeWarning::class, 'hr_manager_id');
    }
    public function violations()
    {
        return $this->hasMany(EmployeeViolation::class);
    }
    public function cashierMachine()
    {
        return $this->belongsTo(CashierMachine::class);
    }

    public function Performance()
    {
        return $this->hasMany(PerformanceReview::class, 'employee_id');
    }

    public function employeeDocument()
    {
        return $this->hasMany(EmployeeDocumentManagement::class, 'employee_id');
    }
    public function attendanceRecords()
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function leaveTypes_employees()
    {
        return $this->belongsToMany(LeaveType::class, 'employee_leaves', 'employee_id', 'leave_type_id')
            ->withPivot('day_count', 'day_paid', 'day_unpaid') // if you want to access extra columns
            ->withTimestamps();
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    public function payrollSetting()
    {
        return $this->hasOne(EmployeePayrollSetting::class, 'employee_id');
    }

    public function paymentTypes()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }

    public function paymentFrequencies()
    {
        return $this->belongsTo(PaymentFrequency::class, 'payment_frequency_id');
    }

    public function activePayrollSetting($date = null)
    {
        $date = $date ?? now()->toDateString();

        return $this->payrollSetting()
            ->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            })
            ->first();
    }
    public function roles()
    {
        return $this->morphToMany(Role::class, 'model', 'model_has_roles');
    }
    public function inventoryStores()
    {
        return $this->belongsToMany(
            Store::class,
            'inventory_employees',
            'employee_id',
            'store_id'
        )->withPivot(['position', 'department']);
    }

    public function employeeRates()
    {
        return $this->hasMany(EmployeeRate::class, 'employee_id');
    }

    public function employeeFacility()
    {
        return $this->hasOne(EmployeeFacility::class, 'employee_id')->where('is_active', 1);

    }
}
