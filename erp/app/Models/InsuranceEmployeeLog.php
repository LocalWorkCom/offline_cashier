<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsuranceEmployeeLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'insurance_employee_logs';

    protected $fillable = [
        'insurance_employee_id',
        'action',
        'log_values',
        'log_timestamp',
        'created_by',
        'created_at',
        'deleted_at',
        'updated_at',
    ];

    protected $casts = [
        'log_values' => 'array',
        'log_timestamp' => 'datetime',
    ];

    protected $hidden = [
        'deleted_at',
        'updated_at',
    ];

    public function insuranceEmployee(): BelongsTo
    {
        return $this->belongsTo(InsuranceEmployee::class, 'insurance_employee_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
