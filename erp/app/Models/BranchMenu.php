<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class BranchMenu extends Model
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

    protected $appends = ['name', 'id_menus_integrations'];

    protected $fillable = [
        'branch_id',
        'dish_id',
        'price',
        'branch_menu_category_id',
        'is_menus_integration',
        'menus_integration_ids',
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

    public function getNameAttribute()
    {
        if (!$this->dish) {
            return null; // Or return a default value like 'Unknown Dish'
        }

        return request()->header('lang', 'ar') === 'en' ? $this->dish->name_en : $this->dish->name_ar;
    }

    public function getIdMenusIntegrationsAttribute()
    {
        if (!$this->menus_integration_ids) {
            return [];
        }

        $menu_ids = json_decode($this->menus_integration_ids, true);

        if (!is_array($menu_ids) || count($menu_ids) === 0) {
            return [];
        }

        $dish_id = $this->id;

        // $MenusIntegration = MenusIntegration::with('menusIntegrationDishs','menusIntegrationDishAddons','menusIntegrationDishSizes')->whereIn('id', $menu_ids)->get();
        $MenusIntegration = MenusIntegration::with([
            'menusIntegrationDishs' => function ($q) use ($dish_id) {
                $q->where('branch_menu_id', $dish_id);
            },
            'menusIntegrationDishAddons' => function ($q) use ($dish_id) {
                $q->where('branch_menu_id', $dish_id);
            },
            'menusIntegrationDishSizes' => function ($q) use ($dish_id) {
                $q->where('branch_menu_id', $dish_id);
            },
        ])
        ->whereIn('id', $menu_ids)
        // ->whereHas('menusIntegrationDishs', function ($q) use ($dish_id) {
        //     $q->where('dish_id', $dish_id);
        // })
        ->get();

        return $MenusIntegration;
    }

    public function dishCategory()
    {
        return $this->belongsTo(DishCategory::class, 'category_id');
    }

    // Define the relationship to Cuisine (if needed)
    public function cuisine()
    {
        return $this->belongsTo(Cuisine::class, 'cuisine_id'); // Adjust 'cuisine_id' as needed
    }

    // Relationships
    public function branches()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function branchMenuCategories()
    {
        return $this->belongsTo(BranchMenuCategory::class, 'branch_menu_category_id');
    }

    public function dish()
    {
        return $this->belongsTo(Dish::class, 'dish_id');
    }

    public function branchMenuAddons()
    {
        return $this->hasMany(BranchMenuAddon::class, 'dish_id', 'dish_id');
    }

    public function branchMenuSizes()
    {
        return $this->hasMany(BranchMenuSize::class, 'dish_id', 'dish_id');
    }

    // public function menusIntegrationDishes()
    // {
    //     return $this->hasMany(MenusIntegrationDish::class, 'branch_menu_id', 'id');
    // }

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

    public function menusIntegrationDishs()
    {
        return $this->hasMany(MenusIntegrationDish::class, 'branch_menu_id', 'id');
    }

    public function menusIntegrationDishSizes()
    {
        return $this->hasMany(MenusIntegrationDishSize::class, 'branch_menu_id', 'id');
    }

    public function menusIntegrationDishAddons()
    {
        return $this->hasMany(MenusIntegrationDishAddon::class, 'branch_menu_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function country()
    {
        return $this->hasOneThrough(
            Country::class,
            Branch::class,
            'id',          // Foreign key on Branch table
            'id',          // Foreign key on Country table
            'branch_id',   // Local key on BranchMenu table
            'country_id'   // Local key on Branch table
        );
    }
}
