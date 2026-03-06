<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class CuisineCategory extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('cuisine-category');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    // Table name if it's not the plural form of the model
    protected $table = 'cuisines_categories';

    // Fillable fields
    protected $fillable = [
        'dish_category_id',
        'cuisine_id',
        'created_by',
        'updated_by',
    ];

    // Soft delete
    protected $dates = ['deleted_at'];

    public function dish_category()
    {
        return $this->belongsTo(DishCategory::class, 'dish_category_id');
    }

    // In CuisineCategory.php
    // public function dishes()
    // {
    //     return $this->hasMany(Dish::class); // or whatever your relationship is
    // }
    public function dishes()
    {
        return $this->hasMany(Dish::class, 'category_id', 'dish_category_id')
            ->whereColumn('cuisine_id', 'cuisine_id');
        // Note: adjust if your Dish's cuisine_id matches CuisineCategory's cuisine_id.
    }
    // Relationship with CuisineCategory
    public function cuisine()
    {
        return $this->belongsTo(Cuisine::class, 'cuisine_id');
    }

    public function chefs()
    {
        return $this->hasMany(ChefCuisineCategory::class, 'cuisine_category_id');
    }
}
