<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostCenterLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cost_center_logs';

    protected $fillable = [
        'cost_center_id',
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

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
