<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayrollSheetItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'payroll_sheet_id',
        'employee_id',
        'base_salary',
        'overtime',
        'deductions',
        'bonuses',
        'net_salary',
        'calculation_details',
        'status',
        'comments',
        'salary_advance',
    ];

    public function sheet()
    {
        return $this->belongsTo(PayrollSheet::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
