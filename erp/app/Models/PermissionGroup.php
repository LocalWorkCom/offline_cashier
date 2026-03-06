<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class PermissionGroup extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('penalty-reason');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $table = 'permission_groups';

    protected $fillable = [
        'name_en',
        'name_ar',
    ];
    protected $append = ['name'];

    /**
     *  Relationship: a group has many permissions
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class, 'group_id');
    }

    /**
     *  Scope: get only active permissions in the group
     */
    public function activePermissions()
    {
        return $this->permissions()->where('is_active', 1);
    }

    /**
     *  Accessor to get the localized name automatically
     */
    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->reason_en : $this->reason_ar;
    }
}
