<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class DishDiscount extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('dish-discount');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $table = 'dish_discount';

    protected $fillable = [
        'dish_id',
        'discount_id',
        'created_by'
    ];

    protected $hidden = [
        'created_by',
        'modify_by',
        'deleted_at',
        'deleted_by',
        'created_at',
        'updated_at',
    ];

    public function dish()
    {
        return $this->belongsTo(Dish::class, 'dish_id')->whereNull('deleted_at');
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class, 'discount_id')->whereNull('deleted_at');
    }
    public function sliders() { 
        return $this->hasMany(Slider::class,'discount_id'); 
    }
}
