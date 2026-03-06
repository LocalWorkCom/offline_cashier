<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class PricingDealItem  extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('price-deal');
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
        'pricing_deal_id',
        'product_id',
        'brand_id',
        'category_id',
        'unit_id',
        'quantity',
        'unit_price',
        'discount',
        'tax',
        'net_amount',
        'notes',
    ];

    /*************************
     *      RELATIONS
     *************************/

    // PricingDeal
    public function pricingDeal()
    {
        return $this->belongsTo(PricingDeal::class);
    }

    // Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Brand
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    // Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Unit
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
