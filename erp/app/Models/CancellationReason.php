<?php

namespace App\Models;

use App\Models\OrderCancellationReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class CancellationReason extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('cancellation-reason');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $table = 'cancellation_reasons';

    protected $fillable = [
        'type',
        'order_id',
        'reason_id',
        'reason',
        'user_id',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    /**
     * Get the order associated with the cancellation reason.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function reasonModel()
    {
        return $this->belongsTo(OrderCancellationReason::class, 'reason_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    /**
     * Get the user who created the cancellation reason.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last modified the cancellation reason.
     */
    public function modifier()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    /**
     * Get the user who deleted the cancellation reason.
     */
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
    // In your CancellationReason model
    public function getLocalizedReasonAttribute()
    {
        if (!$this->reasonModel) {
            return __('order.no_reason_specified');
        }

        return app()->isLocale('ar')
            ? $this->reasonModel->reason_ar
            : $this->reasonModel->reason_en;
    }
}
