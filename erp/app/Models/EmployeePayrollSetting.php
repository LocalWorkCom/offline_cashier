<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePayrollSetting extends Model
{
    use HasFactory;

    // Define the table name (if different from the default pluralized name)
    protected $table = 'employee_payroll_settings';

    // Define the fillable columns
    protected $fillable = [
        'employee_id',

        'payment_frequency_id',
        'salary_value',
        'effective_from',
        'effective_to'
    ];

    // Define the data types for the timestamps
    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'salary_value' => 'decimal:2'
    ];

    // Establish a relationship with the Employee model
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }



    public function paymentFrequency()
    {
        return $this->belongsTo(PaymentFrequency::class);
    }

    // Optionally, you can add custom accessors or mutators if needed
    // Example: Accessor for formatted salary_value
    // public function getFormattedSalaryValueAttribute()
    // {
    //     return '$' . number_format($this->salary_value, 2);
    // }

    // // Example: Mutator to ensure salary_value is always stored as a float
    // public function setSalaryValueAttribute($value)
    // {
    //     $this->attributes['salary_value'] = floatval($value);
    // }
}
