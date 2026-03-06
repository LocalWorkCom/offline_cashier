<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacilityBranchLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'facility_branch_id',
        'action',
        'log_values',
        'log_timestamp',
        'created_by',
    ];

    protected $casts = [
        'log_values' => 'array',
        'log_timestamp' => 'datetime',
    ];

    public function facilityBranch(): BelongsTo
    {
        return $this->belongsTo(FacilityBranch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
