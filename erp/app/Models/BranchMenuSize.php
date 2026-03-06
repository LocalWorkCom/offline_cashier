<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class BranchMenuSize extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('branch-menu-size');
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
        'dish_id',
        'branch_id',
        'dish_size_id',
        'price',
        'is_active',
        'created_by',
        'modified_by',
        'deleted_by',
        'deleted_at',
        'created_at',
        'updated_at',
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
        $integrationIds = $this->menusIntegrationDishSizes()
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

    public function dishSizes()
    {
        return $this->belongsTo(DishSize::class, 'dish_size_id');
    }

    public function dishSizeDefault()
    {
        return $this->hasOne(DishSize::class, 'dish_size_id')->where('default_size', 1);
    }

    public function dishes()
    {
        return $this->belongsTo(Dish::class, 'dish_id');
    }

    public function menusIntegrationDishSizes()
    {
        return $this->hasMany(MenusIntegrationDishSize::class, 'branch_menu_size_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

}
