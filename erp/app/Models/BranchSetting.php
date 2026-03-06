<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class BranchSetting extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('branch-setting');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $table = 'branch_settings';

    protected $fillable = [
        'branch_id',

        // 'no_show_fee',
        // 'deposit_deduction_percentage',
        // 'refund_policy',
        // 'refund_percentage',
        // 'auto_cancel_time_limit',
        // 'table_cancelation_time_if_1',
        // 'table_cancelation_value_if_1',

        'deposit_without_order_deduction_policy', //new 	enum('none', 'full', 'part')
        'deposit_without_order_deduction_percentage', //new  decimal(5,2)
        'deposit_with_order_deduction_policy', //new 	enum('none', 'full', 'part')
        'deposit_with_order_deduction_percentage', //new  decimal(5,2)
        'full_paid_order_deduction_policy', //new  	enum('none', 'full', 'part')
        'full_paid_order_deduction_percentage', //new decimal(5,2)
        'alert_before_arrival_minutes',
        'alert_after_arrival_minutes',
        'table_session_minutes',
        'takeaway_session_minutes',
        'takeaway_deposit_value_if_1',
        'table_cancelation_time_allowed', // new instead of table_cancelation_time_if_1
        'capacity_takeaway',
        'table_reservation_deposit',
        'order_reservation_deposit',
        'full_payment_preparation_setting',
        'no_payment_preparation_setting',
        'deposit_preparation_setting',
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
    ];

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

    public function getOrderTypeAttribute()
    {
        // Retrieve all payment policies for this branch
        $policies = PaymentPolicies::where('branch_id', $this->branch_id)
            ->whereNull('deleted_at')
            ->get();

        // Filter policies based on specific conditions for each order type
        $filteredPolicies = $policies->filter(function ($policy) {
            return (
                // Dine-in condition
                ($policy->order_type === 'dine-in' && $this->table_cancelation_time_allowed) ||

                // Takeaway condition
                ($policy->order_type === 'takeaway' && $this->takeaway_deposit_value_if_1) ||

                // Delivery condition
                ($policy->order_type === 'delivery' && $this->takeaway_deposit_value_if_1) ||

                // Reservation with order condition
                ($policy->order_type === 'reservation_with_order' && $this->table_cancelation_time_allowed) ||

                // Reservation without order condition
                ($policy->order_type === 'reservation_without_order' && $this->table_cancelation_time_allowed)
            );
        });

        // Return the first valid order_type or null if none is valid
        return $filteredPolicies->first()?->order_type;
    }
}
