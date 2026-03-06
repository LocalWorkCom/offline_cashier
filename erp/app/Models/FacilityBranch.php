<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacilityBranch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'facility_id',
        'branch_id',
        'is_active',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];


    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'modified_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'deleted_by');
    }

    public function logs()
    {
        return $this->hasMany(FacilityBranchLog::class);
    }
}
