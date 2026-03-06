<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class PaymentType extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'payment_types';

    protected $fillable = [
        'name_ar',  // mandatory
        'name_en',  // mandatory
        'type',            // ENUM: 'Deposit Billing','Without Deposit','Advanced Payment','Deferred Payment','Periodic Payment'
        'description_ar',       // optional
        'description_en',       // optional
        'status',               // 0 or 1
        'all_vendors',          // 1 = all vendors
        'vendor_ids',           // JSON array of vendor IDs
        'deposit',     // mandatory %
        'payment_interval_id',  // optional foreign key
        'max_delay_percent',    // optional
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    protected $casts = [
        'vendor_ids' => 'array',
        'status' => 'boolean',
        'all_vendors' => 'integer',
        'deposit' => 'integer',
        'max_delay_percent' => 'integer',
    ];

    protected $hidden = [
        'created_by',
        'modified_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = ['name', 'description'];

    /**
     * Return localized name attribute
     */

    public function getNameAttribute($value)
    {
        return Request()->header('lang') == "en" ? $this->name_en : $this->name_ar;
    }

    public function getDescriptionAttribute($value)
    {
        return Request()->header('lang') == "en" ? $this->description_en : $this->description_ar;
    }
    /**
     * Relationship with PaymentInterval
     */
    public function paymentInterval()
    {
        return $this->belongsTo(PaymentInterval::class);
    }
    public function vendors()
    {
        return Vendor::whereIn('id', $this->vendor_ids ?? []);
    }
    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('payment-type');
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(Employee::class, 'modify_by');
    }
}
