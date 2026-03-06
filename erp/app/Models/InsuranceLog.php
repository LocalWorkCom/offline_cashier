<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsuranceLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'insurance_logs';

    protected $fillable = [
        'insurance_id',
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

    public function insurance(): BelongsTo
    {
        return $this->belongsTo(Insurance::class, 'insurance_id', 'id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
