<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Branch extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('branch');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $table = 'branches';

    protected $appends = ['name', 'address', 'name_site', 'address_site', 'is_open', 'is_branch_open'];

    protected $fillable = [
        'street',
        'buildingNumber',
        'category_ids',
        'name_en',
        'name_ar',
        'address_en',
        'address_ar',
        'latitute',
        'longitute',
        'country_id',
        'city_id',
        'area_id',
        'phone',
        'email',
        'employee_id',
        'business_activity_id',
        'company_profile_setting_id',
        'service_fees_type',
        'manager_name',
        'opening_hour',
        'closing_hour',
        'has_kids_area',
        'is_delivery',
        'is_default',
        'tax_application',
        'coupon_application',
        'tax_percentage',
        'time_cancellation',
        'delivery_time',
        'service_fees',
        'delivery_fees',
        'tax_apply',
        'is_active',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
        'is_table_reservation',
        'is_takeaway',
        'is_live',
        'time',
        'code'
    ];

    protected $hidden = [
        'created_by',
        'modified_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    // Cast service_fees and delivery_fees to double
    protected $casts = [
        'service_fees' => 'double',
        'delivery_fees' => 'double',
        'tax_percentage' => 'double',
        'is_active' => 'boolean',
        'is_delivery' => 'boolean',
        'is_table_reservation' => 'boolean',
        'is_takeaway' => 'boolean',
        'has_kids_area' => 'boolean',
        'tax_application' => 'boolean',
        'coupon_application' => 'boolean',
        'tax_apply' => 'boolean',
        'is_default' => 'boolean',
        'is_live' => 'boolean',
    ];

    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }
    public function getNameAttribute2()
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_ar;
    }

    public function getAddressAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->address_en : $this->address_ar;
    }

    public function getNameSiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_ar;
    }

    public function getAddressSiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->address_en : $this->address_ar;
    }

    public function getIsOpenAttribute()
    {
        $currentTime = \Carbon\Carbon::now();
        $currentDay = $currentTime->dayOfWeek; // 0 (Sunday) to 6 (Saturday)

        $branchTime = $this->branchTimeItem($currentDay)->first();

        // If no branch time exists for the current day, return false
        if (!$branchTime) {
            return false;
        }

        $openingTime = \Carbon\Carbon::parse($branchTime->opening_hour);
        $closingTime = \Carbon\Carbon::parse($branchTime->closing_hour);

        // Handle cross-day (e.g., closing past midnight)
        if ($branchTime->cross_day) {
            $closingTime = $closingTime->addDay(); // Extend closing to next day
        }

        // Check if the current time is within the opening and closing times
        return $currentTime->between($openingTime, $closingTime);
    }

    public function getIsBranchOpenAttribute()
    {
        $currentTime = time(); // Current Unix timestamp
        $currentDay = date('w'); // 0 (Sunday) to 6 (Saturday)

        $branchTime = $this->branchTimeItem($currentDay)->first();

        if (!$branchTime) {
            return false;
        }

        $todayDate = date('Y-m-d');
        $openingTime = strtotime($todayDate . ' ' . $branchTime->opening_hour);
        $closingTime = strtotime($todayDate . ' ' . $branchTime->closing_hour);

        if ($branchTime->cross_day && $closingTime <= $openingTime) {
            $closingTime = strtotime('+1 day', $closingTime);
        }

        // return ($currentTime >= $openingTime && $currentTime <= $closingTime);
        return ($currentTime <= $closingTime);
    }

    // Relationships
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function area()
    {
        return $this->belongsTo(Area::class);
    }


    public function employess()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    public function employees()
    {
        return $this->hasMany(Employee::class, 'branch_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    public function recipes()
    {
        return $this->belongsToMany(Recipe::class, 'branch_recipe');
    }

    public function dishes()
    {
        return $this->belongsToMany(Dish::class, 'branch_dish');
    }

    public function floors()
    {
        return $this->hasMany(Floor::class, 'branch_id');
    }

    public function branchTimes()
    {
        return $this->hasMany(BranchTime::class, 'branch_id');
    }

    public function branchTimeDay()
    {
        return $this->hasOne(BranchTime::class, 'branch_id')->where('day', date('w'));
    }

    public function branchRegions()
    {
        return $this->hasMany(BranchRegion::class, 'branch_id');
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'branch_discount', 'branch_id', 'discount_id');
    }
    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'branch_coupon', 'branch_id', 'coupon_id');
    }

    public function branchTimeItem($currentDay)
    {
        return $this->hasOne(BranchTime::class, 'branch_id')->where('day', $currentDay);
    }

    public function branchMenus()
    {
        return $this->hasMany(BranchMenu::class, 'branch_id');
    }

    public function branchCoupons()
    {
        return $this->hasMany(BranchCoupon::class, 'branch_id');
    }

    public function branchDiscounts()
    {
        return $this->hasMany(BranchDiscount::class, 'branch_id');
    }

    public function offers()
    {
        return $this->hasMany(Offer::class, 'branch_id');
    }

    public function offerBranches()
    {
        return $this->hasMany(Offer::class, 'branch_id');
    }

    public function getOffers()
    {
        return Offer::whereIn('branch_id', [-1, $this->id])->get();
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'branch_id');
    }

    public function cashierMachines()
    {
        return $this->hasMany(CashierMachine::class, 'branch_id');
    }

    public function paymentPolicies()
    {
        return $this->hasMany(PaymentPolicies::class, 'branch_id');
    }

    public function department()
    {
        return $this->hasMany(Department::class, 'branch_id');
    }

    public function getTables()
    {
        return $this->hasMany(Floor::class, 'branch_id')->with(['tables' => function ($q) {
            $q->where('status', 1)->where('online', 1);
        }])->get()->pluck('tables')->flatten();
    }

    public function getAllTables()
    {
        return $this->hasMany(Floor::class, 'branch_id')->with(['tables' => function ($q) {
            $q->where('online', 1);
        }])->get()->pluck('tables')->flatten();
    }

    public function getFloorPartitions()
    {
        return $this->hasMany(Floor::class, 'branch_id')->with('floorPartitions');
    }
    public function branchSettings()
    {
        return $this->hasOne(BranchSetting::class, 'branch_id');
    }
    function company()
    {
        return $this->belongsTo(CompanyProfileSetting::class, 'company_profile_setting_id');
    }
    public function businessActivity()
    {
        return $this->belongsTo(BusinessActivity::class, 'business_activity_id');
    }
   
}
