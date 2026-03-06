<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('purchase-order');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $fillable = [
        'pr_number',
        'employee_name',
        'employee_code',
        'has_new_product',
        'department_id',
        'store_id',
        'vendor_id',
        'status',
        'type',
        'period',
        'priority',
        'receive_date',
        'note',
        'pr_date',
        'reject_reason_id',
        'reason_pr_id',
        'created_by',
        'created_by_type',
        'modified_by',
        'modified_by_type',
        'deleted_by',
        'deleted_by_type',
        'approved_by',
        'approved_by_type',
        'rejected_by',
        'rejected_by_type',
        'submitted_by',
        'submitted_by_type',
        'approved_at',
        'rejected_at',
        'submitted_at',
    ];
    protected $dates = [
        'pr_date',
        'receive_date',
        'approved_at',
        'rejected_at',
        'submitted_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'created_by_type',
        'modified_by',
        'modified_by_type',
        'deleted_by',
        'deleted_by_type',
        'approved_by',
        'approved_by_type',
        'rejected_by',
        'rejected_by_type',
        'submitted_by',
        'submitted_by_type',
    ];
    protected $casts = [
        'status' => 'string',
        'pr_date' => 'date',
        'receive_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];
    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }
      public function damyProducts()
    {
        return $this->hasMany(DamyProduct::class, 'pr_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    // Vendor
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id')->withTrashed();
    }

    // Reject reason
    public function rejectReason()
    {
        return $this->belongsTo(RejectReason::class, 'reject_reason_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // Reason PR
    public function reason()
    {
        return $this->belongsTo(ReasonPurchaseRequest::class, 'reason_pr_id');
    }
    public function purchaseOrder()
    {
        return $this->hasMany(PurchaseOrder::class, );
    }
    // Created by (morph to handle multiple user types)
    public function createdBy()
    {
        return $this->morphTo(__FUNCTION__, 'created_by_type', 'created_by');
    }

    // Modified by
    public function modifiedBy()
    {
        return $this->morphTo(__FUNCTION__, 'modified_by_type', 'modified_by');
    }

    // Deleted by
    public function deletedBy()
    {
        return $this->morphTo(__FUNCTION__, 'deleted_by_type', 'deleted_by');
    }

    // Approved by
    public function approvedBy()
    {
        return $this->morphTo(__FUNCTION__, 'approved_by_type', 'approved_by');
    }

    // Rejected by
    public function rejectedBy()
    {
        return $this->morphTo(__FUNCTION__, 'rejected_by_type', 'rejected_by');
    }

    // Submitted by
    public function submittedBy()
    {
        return $this->morphTo(__FUNCTION__, 'submitted_by_type', 'submitted_by');
    }

}
