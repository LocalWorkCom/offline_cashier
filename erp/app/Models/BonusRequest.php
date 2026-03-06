<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'employee_id',
        'bonus_type',
        'bonus_value', // <- Add this line
        'reason',
        'payout_date',
        'created_by',
        'status',
    ];


    // Optional: Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
