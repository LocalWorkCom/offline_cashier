<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeFacility extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'facility_id',
        'is_active',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
    ];

    public function emplyoee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
