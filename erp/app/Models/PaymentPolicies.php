<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class PaymentPolicies extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('payment-policies');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $table = 'payment_policies';

    protected $fillable = [
        'branch_id',
        'order_type',
        'no_payment_required',
        'deposit_required',
        'full_payment_required',
        'table_cancelation_value_type',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
    ];

    protected $hidden = [
        'created_by',
        'modified_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
    ];

    // Add this to make invoice_count accessible
    protected $appends = ['invoice_count'];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function invoiceCount()
    {
        return $this->hasOne(PaymentPolicyInvoiceCount::class, 'payment_policy_id');
    }

    public function getInvoiceCountAttribute()
    {
        // If the relationship is already loaded, use it safely
        if ($this->relationLoaded('invoiceCount')) {
            return optional($this->getRelation('invoiceCount'))->invoice_count ?? 0;
        }

        // Otherwise, load it directly
        $invoiceCount = $this->invoiceCount()->first();
        return $invoiceCount ? $invoiceCount->invoice_count : 0;
    }
}
