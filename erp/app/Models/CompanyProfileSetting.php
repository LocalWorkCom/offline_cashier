<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class CompanyProfileSetting extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('company-profile-setting');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $appends = ['name', 'description', 'name_site'];

    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'business_activity',
        'logo',
        'trade_license',
        'license_expiry_date',
        'tax_registration_number',
        'capital',
        'scanned_trade_license',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    protected $hidden = [
        'created_by',
        'modified_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }
    public function getDescriptionAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->description_en : $this->description_ar;
    }

    public function getNameSiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_ar;
    }

    public function businessActivity()
    {
        return $this->belongsTo(BusinessActivity::class, 'business_activity');
    }

    public function socialMediaInformation()
    {
        return $this->hasMany(SocialMediaInformationSetting::class, 'company_id');
    }

    public function companyPolicy()
    {
        return $this->hasMany(CompanyPolicy::class, 'company_id');
    }

    public function branch()
    {
        return $this->hasMany(Branch::class, 'company_profile_setting_id');
    }

    public function contactInformationSetting()
    {
        return $this->hasMany(ContactInformationSetting::class, 'company_id');
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


}
