<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class OrderRequest extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('order_requests');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
  protected $table = 'order_requests';

    protected $fillable = [
        'request_type',
        'source_order_id',
        'target_order_id',
        'requested_by_id',
        'requested_by_role',
        'branch_id',
        'status',
        'reason',
        'approved_by',
    ];

    protected $casts = [
        'request_type' => 'string',
        'requested_by_role' => 'string',
        'status' => 'string',
    ];

    public function sourceOrder()
    {
        return $this->belongsTo(Order::class, 'source_order_id');
    }

    // Target order (for merge / split)
    public function targetOrder()
    {
        return $this->belongsTo(Order::class, 'target_order_id');
    }

    // Branch
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // User who requested (waiter / cashier)
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    // User who approved the request
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}