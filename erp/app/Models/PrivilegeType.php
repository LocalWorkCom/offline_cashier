<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class PrivilegeType extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('product-color');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $appends = ['name', 'name_site', 'reason'];
    protected $fillable = [
        'name_ar',
        'name_en',
        'reason_ar',
        'reason_en',
        'is_active',
        'created_by',
        'created_by_type',
        'modified_by',
        'modified_by_type',
        'deleted_by',
        'deleted_by_type',

    ];
    protected $hidden = [
        'name_ar',
        'name_en',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'created_by_type',
        'modified_by',
        'modified_by_type',
        'deleted_by',
        'deleted_by_type',
    ];
    public function getNameAttribute($value)
    {
        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }
    public function getReasonAttribute($value)
    {
        return request()->header('lang', 'ar') === 'en' ? $this->reason_en : $this->reason_ar;
    }

    public function getNameSiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_ar;
    }
}
