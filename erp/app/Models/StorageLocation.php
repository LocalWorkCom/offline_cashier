<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class StorageLocation extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('storage-location');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $table = 'storage_locations';

    protected $appends = ['name', 'description'];

    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type'
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
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
        'deleted_at'
    ];
    public function getNameAttribute($value)
    {
        return Request()->header('lang') == "en" ? $this->name_en : $this->name_ar;
    }
    public function getDescriptionAttribute($value)
    {
        return Request()->header('lang') == "en" ? $this->description_en : $this->description_ar;
    }

    public function warehouses()
    {
        return $this->belongsToMany(Store::class, 'storage_location_warehouse')
            ->withTimestamps();
    }
    public function productStores()
    {
        return $this->hasMany(ProductStore::class, 'store_id');
    }

    public function productBrands()
    {
        return $this->belongsToMany(
            ProductBrand::class,
            'product_stores',   // pivot table
            'store_id',         // FK on pivot table
            'product_brand_id'  // related FK
        )->whereNotNull('product_brand_id');
    }
}
