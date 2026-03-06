<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class BranchMenuCategory extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('branch-menu-category');
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
        'dish_category_id',
        'parent_id',
        'branch_id',
        'is_active',
        'created_by',
        'modified_by',
        'deleted_by',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->dish_categories->name_en : $this->dish_categories->name_ar;
    }

    public function branches()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function dish_categories()
    {
        return $this->belongsTo(DishCategory::class, 'dish_category_id');
    }

    public function branchMenus()
    {
        return $this->hasMany(BranchMenu::class, 'branch_menu_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
