<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Zone extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('zone');
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
        'store_id',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'max_temperature',
        'min_temperature',
        'storage_location_id',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type'
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
    protected $dates = ['deleted_at'];
    protected $casts = [
        'storage_location_id' => 'array',
    ];

    protected $appends = ['storage_locations_data'];

    public function getStorageLocationsDataAttribute()
    {
        if (empty($this->storage_location_id)) {
            return [];
        }

        return StorageLocation::whereIn('id', $this->storage_location_id)->get();
    }

    public function warehouse()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function rackShelves()
    {
        return $this->hasMany(RackShelf::class);
    }
    public function productStores()
    {
        return $this->hasMany(ProductStore::class, 'zone_id');
    }

    // A zone has many audits
    public function audits()
    {
        return $this->hasMany(Audit::class, 'zone_id');
    }
}
