<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class MenusIntegration extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('branch-menu');
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

    protected $hidden = ['is_active', 'created_by', 'modified_by', 'deleted_by', 'created_by_type', 'modified_by_type', 'deleted_by_type', 'deleted_at', 'created_at', 'updated_at'];

    public function getNameAttribute($value){
        return Request()->header('lang') == "en" ? $this->name_en : $this->name_ar;
    }

    public function getNameSiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_ar;
    }


    public function menusIntegrationDishs()
    {
        return $this->hasMany(MenusIntegrationDish::class, 'menus_integration_id', 'id');
    }

    public function menusIntegrationDishAddons()
    {
        return $this->hasMany(MenusIntegrationDishAddon::class, 'menus_integration_id', 'id');
    }

    public function menusIntegrationDishSizes()
    {
        return $this->hasMany(MenusIntegrationDishSize::class, 'menus_integration_id', 'id');
    }

    public function menusDishs()
    {
        return $this->hasMany(MenusIntegrationDish::class, 'menus_integration_id', 'id');
    }

}
