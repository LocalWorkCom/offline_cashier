<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class Recipe extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('recipe');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $appends = ['name', 'description', 'name_site', 'description_site'];
    protected $fillable = [
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'meal_type',
        'code',
        'type', // 1 for recipe, 2 for addon
        'time',
        'item_code_id',
        // 'price',
        'is_active',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    protected $hidden = [
        'name_en',
        'name_ar',
        'meal_type',
        'description_en',
        'description_ar',
        'created_by',
        'modified_by',
        'deleted_by',
         'created_by_type',
        'modified_by_type',
        'deleted_by_type',
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

    public function getDescriptionSiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->description_en : $this->description_ar;
    }

    // Scope to filter recipes only (type = 1)
    public function scopeOnlyRecipes($query)
    {
        return $query->where('type', 1);
    }

    // Scope to filter addons only (type = 2)
    public function scopeOnlyAddons($query)
    {
        return $query->where('type', 2);
    }

    // Relationships
    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }

    public function images()
    {
        return $this->hasMany(RecipeImage::class);
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

    public function addons()
    {
        return $this->hasMany(DishAddon::class, 'addon_id');
    }
    public function dishDetails()
    {
        return $this->hasMany(DishDetail::class, 'recipe_id')
        ->whereHas('dish', function ($query) {
            $query->whereNull('deleted_at'); // Check if the associated offer is not deleted
        });;
    }
    public function hasDishRelation()
    {
        return $this->dishDetails()->exists();
    }
    public function itemCode()
    {
        return $this->belongsTo(ItemCode::class);
    }
}
