<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class PaymentMethod extends Model
{
    use HasFactory, LogsActivity;

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
    protected $table = 'payment_methods';

    protected $fillable = [
        'type',                 // cash or visa
        'name_ar',// required
        'name_en', // required
        'description_ar',       // optional
        'description_en',       // optional
        'additional_info',      // optional
        'status',               // 0 or 1
        'all_vendors',          // 1 = all vendors
        'vendor_ids',           // JSON array
    ];


    protected $casts = [
        'vendor_ids' => 'array',
        'status' => 'boolean',
        'all_vendors' => 'integer',
    ];

    protected $appends = ['name', 'description'];

    /**
     * Return localized name attribute
     */

    protected $hidden = [
        'created_by',
        'modified_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function getNameAttribute($value)
    {
        return Request()->header('lang') == "en" ? $this->name_en : $this->name_ar;
    }

    public function getDescriptionAttribute($value)
    {
        return Request()->header('lang') == "en" ? $this->description_en : $this->description_ar;
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
