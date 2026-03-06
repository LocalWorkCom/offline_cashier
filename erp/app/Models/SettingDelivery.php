<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class SettingDelivery extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('setting-delivery');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $table = 'setting_delivers';

    // Define the fillable fields for mass assignment
    protected $fillable = [
        'max_order',
        'min_order',
        'delivery_id',
        'vehicle_settings_id',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

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

    // Define relationship with the Employee model
    public function delivery()
    {
        return $this->belongsTo(Employee::class, 'delivery_id');
    }
    // Define relationship with the Employee model
    public function vehicle_settings()
    {
        return $this->belongsTo(VehicleSetting::class, 'vehicle_settings_id');
    }
}
