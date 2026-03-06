<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class Slider extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('slider');
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
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'image',
        'flag',
        'dish_id',
        'offer_id',
        'discount_id',
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

    public function dish()
    {
        return $this->belongsTo(Dish::class, 'dish_id', 'id'); // Inverse of the one-to-many relationship
    }


    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id', 'id');
    }

    public function discount()
    {
        return $this->belongsTo(DishDiscount::class, 'discount_id', 'id');
    }
    public function discounts()
{
    return $this->belongsTo(Discount::class, 'discount_id', 'id');
}
    // public function discount()
    // {
    //     return $this->hasMany(ProductUnit::class);
    // }
}
