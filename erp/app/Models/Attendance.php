<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [
        'employee_id',
        'employee_schedule_id',
        'date',
        'clock_in_time',
        'clock_out_time',
        'total_hours',
        'overtime_hours',
        'late_minutes',
        'early_departure_minutes',
        'biometric_transaction_id',
        'created_by',
        'updated_by',
        'latitude_in',
        'longitude_in',
        'latitude_out',
        'longitude_out',
        'status', // new field for early, late, on_time
    ];

    protected $dates = ['date', 'clock_in_time', 'clock_out_time', 'deleted_at'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function biometricTransaction()
    {
        return $this->belongsTo(BiometricTransaction::class);
    }
    public function suspension()
    {
        return $this->hasOne(TemporarySuspension::class, 'employee_id', 'employee_id');
    }
    public function employeeSchedule()
    {
        return $this->belongsTo(EmployeeSchedule::class, 'employee_schedule_id');
    }
}
