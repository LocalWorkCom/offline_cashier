<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class PaymentInterval extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('payment-method');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $table = 'payment_intervals';

    protected $fillable = [
        'name_ar',
        'name_en',
        'number_of_days',
        'status',
        'all_vendors',
        'vendor_ids',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
    ];

    protected $casts = [
        'vendor_ids' => 'array', // JSON → array
        'status' => 'boolean',
        'all_vendors' => 'integer',
        'number_of_days' => 'integer',
    ];

    protected $appends = ['name'];

    protected $hidden = [
        'created_by',
        'modified_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    /**
     * Return localized name attribute
     */
    public function getNameAttribute($value)
    {
        return Request()->header('lang') == "en" ? $this->name_en : $this->name_ar;
    }

    public function vendors()
    {
        return Vendor::whereIn('id', $this->vendor_ids ?? []);
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
