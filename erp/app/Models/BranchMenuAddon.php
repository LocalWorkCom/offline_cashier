<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class BranchMenuAddon extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('branch-menu-addon');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $table = "branch_menu_addons";
    protected $fillable = [
        'branch_id',
        'dish_addon_id',
        'dish_id',
        'branch_menu_addon_category_id',
        'price',
        'is_active',
        'created_by',
        'modified_by',
        'deleted_by',
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = ['branch_menu_id', 'menus_integration_dish'];

    public function getBranchMenuIdAttribute()
    {
        return BranchMenu::where([
            ['dish_id', '=', $this->dish_id],
            ['branch_id', '=', $this->branch_id],
        ])->value('id') ?? null;
    }

    public function getMenusIntegrationDishAttribute()
    {
        $integrationIds = $this->menusIntegrationDishAddons()
            ->pluck('menus_integration_id')
            ->toArray();

        if (!empty($integrationIds)) {
            return MenusIntegrationDish::whereIn('menus_integration_id', $integrationIds)
                ->where('branch_menu_id', $this->branch_menu_id) // match branch
                ->pluck('id');
        }

        return collect();
    }

    public function branches()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function dishes()
    {
        return $this->belongsTo(Dish::class, 'dish_id');
    }

    public function dishAddons()
    {
        return $this->belongsTo(DishAddon::class, 'dish_addon_id');
    }

    public function branchMenuAddonCategories()
    {
        return $this->belongsTo(BranchMenuAddonCategory::class, 'branch_menu_addon_category_id');
    }

    public function menusIntegrationDishAddons()
    {
        return $this->hasMany(MenusIntegrationDishAddon::class, 'branch_menu_addon_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
