<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductBrand extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('product-brand');
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
        'brand_id',
        'product_id',
        'image',
        'is_reusable',
        'is_have_expired',
        'expiration_type',
        'base_unit_id',
        'default_unit_id'

    ];

    // Relationships
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
    public function productStores()
    {
        return $this->hasMany(ProductStore::class, 'product_brand_id');
    }


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function units()
    {
        return $this->hasMany(ProductBrandUnit::class, 'product_brand_id');
    }
    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }
    public function defaultUnit()
    {
        return $this->belongsTo(Unit::class, 'default_unit_id');
    }
    public function openingBalance()
    {
        return $this->hasOne(ProductOpeningBalance::class, 'product_brand_id');
    }


    public function transactions()
    {
        return $this->hasMany(ProductTransaction::class, 'product_brand_id');
    }
    
}
