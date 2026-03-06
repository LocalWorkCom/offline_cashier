<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class DishAddon extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('dish-addon');
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
        'addon_id',
        'quantity',
        'price',
        'addon_category_id',
        'min_addons',
        'max_addons',
    ];

    protected $casts = [
        'quantity' => 'int',
        'price' => 'float',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function dish()
    {
        return $this->belongsTo(Dish::class, 'dish_id');
    }

    public function addons()
    {
        return $this->belongsTo(Recipe::class, 'addon_id')->where('type', 2);
    }
    

    public function category()
    {
        return $this->belongsTo(AddonCategory::class, 'addon_category_id');
    }
    public function addonCategory()
    {
        return $this->belongsTo(AddonCategory::class, 'addon_category_id', 'id');
    }
}
