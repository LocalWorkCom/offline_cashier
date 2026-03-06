<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductStore extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('product-store');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $table = 'product_stores';

    protected $fillable = [
        'min_limit',
        'max_limit',
        'product_brand_id',
        'store_id',
        'shelve_id',
        'zone_id',
        'is_freeze',
        'storage_location_id',
    ];


    protected $hidden = [
        'created_by',
        'deleted_by',
        'created_at',
        'modify_by',
        'updated_at',
        'deleted_at',

    ];

    public function productBrand()
    {
        return $this->belongsTo(ProductBrand::class, 'product_brand_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
    public function storageLocation()
    {
        return $this->belongsTo(StorageLocation::class, 'storage_location_id');
    }
    public function shelve()
    {
        return $this->belongsTo(RackShelf::class, 'shelve_id');
    }
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }
   
}
