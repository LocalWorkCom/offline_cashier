<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class DishSize extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('dish-size');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $appends = ['name_site', 'name'];

    protected $fillable = [
        'dish_id',
        'size_name_en',
        'size_name_ar',
        'price',
        'default_size',
        'dish_size_id',
        'recipe_id',
        'quantity'
    ];

    public function getNameAttribute()
    {
        
        return request()->header('lang', 'ar') === 'en' ? $this->size_name_en : $this->size_name_ar;
    }

    public function getNameSiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->size_name_en : $this->size_name_ar;
    }

    /**
     * Get the dish associated with the size.
     */
    public function dish()
    {
        return $this->belongsTo(Dish::class);
    }
    public function details()
    {
        return $this->hasMany(DishDetail::class, 'dish_size_id');
    }
    public function recipes()
    {
        return $this->hasMany(DishDetail::class, 'dish_size_id');
    }

    public function branchMenuSizes()
    {
        return $this->hasMany(BranchMenuSize::class, 'dish_size_id', 'id');
    }
}
