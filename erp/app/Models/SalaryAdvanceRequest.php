<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryAdvanceRequest extends Model
{
    use HasFactory;
    protected $appends = ['status'];
    protected $fillable = [
        'employee_id',
        'amount',
        'reason',
        'status',
        'hr_comment',
        'approved_by',
        'approved_by_type',
        'created_by',
        'created_by_type',
        'updated_by',
        'updated_by_type',
        'advance_date',
        'deduction_month'
    ];

    // Relationship with the Employee (User)
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function getStatusAttribute()
    {
        if ($this->advance_date && $this->attributes['status'] == 'approved') {
            return 'paid';
        } else {
            return strtolower($this->attributes['status']);
        }
    }
}
