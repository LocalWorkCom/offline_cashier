<?php

namespace App\Models;

use App\Models\DishCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Cuisine extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('cuisine');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $appends = ['name', 'description', 'name_site', 'description_site', 'image'];

    protected $fillable = [
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'image_path',
        'is_active',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
    ];

    protected $hidden = [
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
                'image',

    ];
 public function getImageAttribute()
    {
        return $this->image_path;
    }

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

    // Relationships
    public function dishes()
    {
        return $this->hasMany(Dish::class);
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

    // Scope to get only active cuisines
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function dishCategories()
    {
        return $this->belongsToMany(DishCategory::class, 'cuisines_categories');
    }
}
