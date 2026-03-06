<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LateDeductionSetting extends Model
{
    protected $table = 'late_deduction_settings';

    protected $fillable = [
        'deduction_mode',   // 'partial' or 'fixed'
        'partial_interval', // e.g. minutes interval for deduction
        'fixed_threshold',  // threshold minutes for fixed deduction
        'deduct_from',      // e.g. 'basic_salary' or 'total_salary'
        'notify_employee',  // boolean flag to notify employee
    ];
}
