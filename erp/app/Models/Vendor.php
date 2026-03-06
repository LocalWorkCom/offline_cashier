<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Vendor extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('vendor');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $appends = ['name'];
    protected $fillable = [
        'name_ar',
        'name_en',
        'type',
        'country_id',
        'phone',
        'email',
        'address',
        'latitude',
        'longitude',
        'communication_method',
        'remaining_credit',
        'credit_balance',
        'rate',
        'is_active',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    protected $casts = [
        'communication_method' => 'array',  // JSON multiple options
        'is_active' => 'integer',
        'remaining_credit' => 'decimal:2',
        'credit_balance' => 'decimal:2',
        'rate' => 'decimal:2',
    ];

    /*--------------------------------------
    | Relationships
    ---------------------------------------*/

    // Vendor → VendorInfo (Company details)
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function info()
    {
        return $this->hasOne(VendorInfo::class);
    }

    // Vendor → Categories (many-to-many via vendor_categories)
    public function categories()
    {
        return $this->hasMany(VendorCategory::class, 'vendor_id');
    }


    public function paymentMethods()
    {
        return $this->belongsToMany(PaymentMethod::class, 'vendor_payment_methods');
    }

    public function paymentTypes()
    {
        return $this->belongsToMany(PaymentType::class, 'vendor_payment_types');
    }


    // Created By Employee
    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    // Updated By Employee
    public function updater()
    {
        return $this->belongsTo(Employee::class, 'modified_by');
    }

    // Deleted By Employee
    public function deleter()
    {
        return $this->belongsTo(Employee::class, 'deleted_by');
    }
    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }
    public function pricingDeals()
    {
        return $this->hasMany(PricingDeal::class);
    }


}
