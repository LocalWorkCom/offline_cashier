<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UUID;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class Country extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, UUID;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('country');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $appends = ['name', 'code', 'currency', 'name_site', 'currency_site'];

    protected $fillable = [
        'name_ar',
        'name_en',
        'code',
        'currency_ar',
        'currency_en',
        'currency_code',
        'currency_symbol',
        'job_years',
        'phone_code',
        'length',
        'order',
        'active_show'
    ];


    protected $hidden = [
        'created_by',
        'deleted_by',
        'modify_by',
        'deleted_at',
        'created_at',
        'updated_at',
        'code',
        'job_years',
        // 'phone_code',
        //'length',


    ];

    public function getNameAttribute()
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_ar;
    }
    public function getCurrencyAttribute($value)
    {
        return Request()->header('lang') == "en" ? $this->currency_en : $this->currency_ar;
    }

    public function getNameSiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_ar;
    }

    public function getCurrencySiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->currency_en : $this->currency_ar;
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
    /**
     * Get the areas for the country.
     */
    public function areas()
    {
        return $this->hasMany(Area::class, 'country_id');
    }

    /**
     * Get the cities for the country.
     */
    public function cities()
    {
        return $this->hasMany(City::class, 'country_id');
    }

    public function university()
    {
        return $this->hasMany(University::class);
    }
    public function militaryServices()
    {
        return $this->hasMany(MilitaryServiceStatus::class);
    }
}
