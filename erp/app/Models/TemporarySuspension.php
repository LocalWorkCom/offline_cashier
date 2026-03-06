<?php

namespace App\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TemporarySuspension extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'start_date',
        'end_date',
        'suspension_duration',
        'reason',
        'approval_status',
        'supporting_documents',
    ];
    protected $hidden = [
        'updated_at',
        'deleted_at',
        'modified_by',
        'deleted_by',
        'modified_by_type',
        'deleted_by_type'
    ];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',

        'supporting_documents' => 'array',
    ];
    // Constants for approval status
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get approval status options
     */
    public static function getApprovalStatusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    /**
     * Check if suspension is approved
     */
    public function isApproved(): bool
    {
        return $this->approval_status === self::STATUS_APPROVED;
    }

    /**
     * Check if suspension is pending
     */
    public function isPending(): bool
    {
        return $this->approval_status === self::STATUS_PENDING;
    }

    /**
     * Check if suspension is rejected
     */
    public function isRejected(): bool
    {
        return $this->approval_status === self::STATUS_REJECTED;
    }

    /**
     * Calculate suspension duration automatically
     */
    public function calculateDuration(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
