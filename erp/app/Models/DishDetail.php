<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class DishDetail extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('dish-detail');
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
        'recipe_id',
        'dish_size_id',
        'quantity',
    ];

    public function dish()
    {
        return $this->belongsTo(Dish::class);
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class)->where('type', 1);
    }
    public function size()
    {
        return $this->belongsTo(DishSize::class, 'dish_size_id');
    }
    public function dishSize()
    {
        return $this->belongsTo(DishSize::class, 'dish_size_id', 'id');
    }

}
