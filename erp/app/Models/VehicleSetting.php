<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class VehicleSetting extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('vehicle-setting');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    // Constants for vehicle types
    const VEHICLE_TYPE_CAR = 'car';
    const VEHICLE_TYPE_MOTORCYCLE = 'motorcycle';

    // Define the fillable fields for mass assignment
    protected $fillable = [
        'vehicle_type',
        'vehicle_max',
        'vehicle_min',
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
        'created_type',
        'deleted_type',
        'modified_type',
    ];

    // Define casts for certain attributes
    protected $casts = [
        'vehicle_type' => 'string',
    ];

    // Method to get the valid vehicle types
    public static function getVehicleTypes()
    {
        return [
            self::VEHICLE_TYPE_CAR,
            self::VEHICLE_TYPE_MOTORCYCLE,
        ];
    }
    // Define relationships with the User model
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'vehicle_type');
    }
}
