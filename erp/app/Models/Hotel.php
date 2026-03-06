<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class Hotel extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('hotel');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $appends = ['name', 'name_site'];

    protected $fillable = [
        'name_ar',
        'name_en',
        'branch_id',
        'description_ar',
        'description_en',
        'address_ar',
        'address_en',
        'country_id',
        'city_id',
        'area_id',
        'phone_number',
        'building_number',
        'branch_name_en',
        'branch_name_ar',
        'status',
        'note',
        'shiping_cost',
        'created_by',
        'modified_by',
        'deleted_by'
    ];

    protected $hidden = [
        'description_ar',
        'description_en',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'modified_by',
        'deleted_by',
    ];
    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }
    public function getNameSiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_ar;
    }
    public function getAddressAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->address_en : $this->address_ar;
    }
    public function getAddressSiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->address_en : $this->address_ar;
    }
    public function addresses()
    {
        return $this->hasMany(ClientAddress::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
    public function area()
    {
        return $this->belongsTo(Country::class, 'area_id');
    }
    public function city()
    {
        return $this->belongsTo(Country::class, 'city_id');
    }

    public function getBranchAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->branch_name_en : $this->branch_name_ar;
    }
    public function getBranchSiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->branch_name_en : $this->branch_name_ar;
    }
}
