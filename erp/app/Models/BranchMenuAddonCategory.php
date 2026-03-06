<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class BranchMenuAddonCategory extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('branch-menu-addon-category');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $fillable = [
        'branch_id',
        'addon_category_id',
        'is_active',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    public function branches()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function addonCategories()
    {
        return $this->belongsTo(AddonCategory::class, 'addon_category_id');
    }

    public function branchMenuAddons()
    {
        return $this->hasMany(BranchMenuAddon::class, 'branch_menu_addon_category_id')->active();
    }

    public function branchMenuAddon()
    {
        return $this->hasOne(BranchMenuAddon::class, 'branch_menu_addon_category_id')->where('branch_id', $this->branch_id);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
