<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Store extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('store');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $appends = ['name'];

    protected $fillable = [
        'branch_id',
        'country_id',
        'city_id',
        'area_id',
        'street',
        'building',
        'name_en',
        'name_ar',
        'longitude',
        'latitude',
        'code',
        'max_storage',
        'min_storage',
        'phone',
        'description_en',
        'description_ar',
        'is_kitchen',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
    ];

    protected $hidden = [
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $dates = ['deleted_at']; // To handle soft deletes

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function area()
    {
        return $this->belongsTo(Area::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }

    public function lines()
    {
        return $this->hasMany(Line::class);
    }
    public function storeCategories()
    {
        return $this->hasMany(StoreCategory::class);
    }

    public function categories()
    {
        return $this->hasManyThrough(Category::class, StoreCategory::class, 'store_id', 'id', 'id', 'category_id');
    }

    public function zones()
    {
        return $this->hasMany(Zone::class, 'store_id');
    }

    public function storageLocations()
    {
        return $this->belongsToMany(StorageLocation::class);
    }

    public function inventoryEmployees()
    {
        return $this->belongsToMany(
            Employee::class,
            'inventory_employees',
            'store_id',
            'employee_id'
        );
    }
    public function productStores()
    {
        return $this->hasMany(ProductStore::class, 'store_id');
    }

    public function productBrands()
    {
        return $this->hasManyThrough(
            ProductBrand::class,
            ProductStore::class,
            'store_id',          // Foreign key on product_stores table
            'id',                // Local key on product_brands table
            'id',                // Local key on stores table
            'product_brand_id'   // Foreign key on product_stores table
        );
    }

    public function products()
    {
        return $this->hasManyThrough(
            Product::class,
            ProductBrand::class,
            'id',         // Foreign key on product_brands table
            'id',         // Local key on products table
            'id',         // Local key on stores table
            'product_id'  // Foreign key on product_brands table
        );
    }
}
