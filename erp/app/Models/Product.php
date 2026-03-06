<?php

namespace App\Models;

use App\Models\Size;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('product');
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
    protected $table = 'products';
    protected $appends = ['name', 'image', 'description'];

    // The attributes that are mass assignable
    protected $fillable = [
        'main_image',
        'type',
        'main_unit_id',
        'currency_id',
        'category_id',
        'sub_category_id',
        'is_have_expired',
        'sku',
        'barcode',
        'code',
        'limit_quantity',
        'is_remind',
        'name_ar',
        'name_en',
        'currency_code',
        'description_ar',
        'description_en',
    ];

    protected $hidden = [
        // 'name_ar',
        // 'name_en',
        // 'description_ar',
        // 'description_en',
        'created_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'modify_by',
        'deleted_at',
        'main_image',
    ];

    public function getNameAttribute($value)
    {
        return Request()->header('lang') == "en" ? $this->name_en : $this->name_ar;
        // return $this->name_en;
    }
    // public function getNameAr()
    // {
    //     self::$staticMakeVisible = ['name_ar'];

    //     return  $this->name_ar;
    //     // return $this->name_en;
    // }
    public function getDescriptionAttribute($value)
    {
        return Request()->header('lang') == "en" ? $this->name_en : $this->name_ar;
    }
    public function getImageAttribute($value)
    {
        return BaseUrl() . '/' . $this->main_image;
    }
    // The attributes that should be cast to native types
    protected $casts = [
        'is_have_expired' => 'boolean',
    ];

    // Define relationships
    public function mainUnit()
    {
        return $this->belongsTo(Unit::class, 'main_unit_id');
    }

    public function currency()
    {
        return $this->belongsTo(Country::class, 'country_code');
    }

    public function Category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function subCategory()
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }
    // public function productUnits()
    // {
    //     return $this->belongsToMany(Unit::class, 'product_units', 'product_id', 'unit_id');
    // }
    public function units()
    {
        return $this->belongsToMany(Unit::class, 'product_units')
            ->withPivot('factor', 'unit_id') // Include all necessary pivot fields
            ->withTimestamps(); // Optional: if your pivot table has timestamps
    }

    public function product_sizes()
    {
        return $this->belongsToMany(Size::class, 'product_sizes')
            ->withPivot('code_size', 'size_id') // Include all necessary pivot fields
            ->withTimestamps(); // Optional: if your pivot table has timestamps
    }

    public function product_colors()
    {
        return $this->belongsToMany(Color::class, 'product_colors')
            ->withPivot('color_id') // Include all necessary pivot fields
            ->withTimestamps(); // Optional: if your pivot table has timestamps
    }
    public function productColors()
    {
        return $this->hasMany(ProductColor::class);
    }

    public function productSizes()
    {
        return $this->hasMany(ProductSize::class);
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class, 'product_colors', 'product_id', 'color_id');
    }

    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'product_sizes', 'product_id', 'size_id');
    }

    public function productUnites()
    {
        return $this->hasOne(ProductUnit::class, 'product_id');
    }

    public function ingredients()
    {
        return $this->hasManyThrough(Ingredient::class, ProductUnit::class, 'product_id', 'product_unit_id');
    }

    public function productStores()
    {
        return $this->hasManyThrough(
            ProductStore::class,
            ProductBrand::class,
            'product_id',
            'product_brand_id',
            'id',
            'id'
        );
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }

    public function getItemCodeNameAttribute()
    {
        $lang = app()->getLocale();
        return $lang === 'ar' ? optional($this->itemCode)->codeNameAr : optional($this->itemCode)->codeName;
    }

    public function productBrands()
    {
        return $this->hasMany(ProductBrand::class);
    }
    public function dspItems()
    {
        return $this->hasMany(DirectSupplyPermissionItem::class, 'item_id');
    }
    public function purchaseOrderItems()
{
    return $this->hasMany(PurchaseOrderItem::class, 'product_id');
}

}
