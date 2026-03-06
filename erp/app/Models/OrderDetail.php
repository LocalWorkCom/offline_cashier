<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class OrderDetail extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('order-detail');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    // Table associated with the model
    protected $table = 'order_details';

    // The attributes that are mass assignable
    protected $fillable = [
        'status',
        'order_id',
        'quantity',
        'total',
        'price_befor_tax',
        'price_after_tax',
        'service_fees',
        'tax_value',
        'note',
        'price_before_coupon',
        'dish_id',
        'dish_size_id',
        'addon_id',
        'unit_id',
        'coupon_id',
        'coupon_value',
        'recipe_id',
        'product_id',
        'order_by', // Added order_by column
        'dish_order',
        'created_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'modify_by',
        'deleted_at',
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'modify_by',
        'deleted_at',
    ];

    protected $appends = ['dish_size_name'];

    public function getDishSizeNameAttribute()
    {
        $lang = request()->header('lang', 'en');
        if ($this->relationLoaded('dishSize') && $this->dishSize) {
            return $lang === 'ar'
                ? $this->dishSize->size_name_ar
                : $this->dishSize->size_name_en;
        }
        // return null;
    }

    // // The attributes that should be cast to native types
    // protected $casts = [
    //     'is_valid' => 'boolean',
    // ];

    // Define relationships
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function dish()
    {
        return $this->belongsTo(Dish::class, 'dish_id')->withTrashed();; // Make sure 'dish_id' is correct
    }


    public function dishAddons()
    {
        return $this->hasMany(OrderAddon::class, 'order_details_id');
    }

    public function dishSize()
    {
        return $this->belongsTo(DishSize::class, 'dish_size_id', 'id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }
}
