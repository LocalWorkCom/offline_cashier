<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsuranceEmployee extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'insurance_employees';

    protected $fillable = [
        'insurance_id',
        'employee_id',
        'amount',
        'date',
        'is_active',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function insurance()
    {
        return $this->belongsTo(Insurance::class, 'insurance_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'id');
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(Employee::class, 'modified_by', 'id');
    }

    public function deletedByUser()
    {
        return $this->belongsTo(Employee::class, 'deleted_by', 'id');
    }
}
