<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class PurchaseOrder extends Model
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
        'po_number',
        'type_po',
        'pr_id',
        'employee_id',
        'category_id',
    'merged_po_id',
    'is_merged',
        'to_department_id',
        'address',
        'lat',
        'long',
        'type',
        'priority',
        'arraival_date',
        'period',
        'total',
        'note_delivery',
        'note',
        'pm_approval_id',
        'pm_approval_at',
        'fm_approval_id',
        'fm_approval_at',
        'status',
        'rejected_at',
        'rejected_from',
        'rejected_by',
        'reject_reason_id',
    ];

    // =======================
    // 🔗 RELATIONS
    // =======================

    public function pr()
    {
        return $this->belongsTo(PurchaseRequest::class, 'pr_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function toDepartment()
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function pmApproval()
    {
        return $this->belongsTo(Employee::class, 'pm_approval_id');
    }

    public function fmApproval()
    {
        return $this->belongsTo(Employee::class, 'fm_approval_id');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(Employee::class, 'rejected_by');
    }

    public function rejectReason()
    {
        return $this->belongsTo(RejectPurchaseRequest::class, 'reject_reason_id');
    }

    // 🔥 Order Items
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'po_id');
    }

    // 🔥 Deals
    public function deals()
    {
        return $this->hasMany(PurchaseOrderDeal::class, 'po_id');
    }
    public function purchaseRequest()
    {
        return $this->belongsTo(purchaseRequest::class, 'pr_id');
    }
}
