<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class Table extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('table');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $fillable = ['online','status']; // Add 'online' here

    protected $appends = ['name', 'name_site', 'available_status'];

    protected $hidden = ['created_by', 'modified_by', 'deleted_by', 'deleted_at', 'updated_at', 'created_at'];
protected $casts = [
    'status' => 'integer'
];
    public function getNameAttribute($value){

        return Request()->header('lang') == "en" ? $this->name_en : $this->name_ar;
    }

    public function getNameSiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_ar;
    }

    public function getAvailableStatusAttribute($value){
        return $this->status == 1 ? true : false;
    }

    public function floors()
    {
        return $this->belongsTo(Floor::class, 'floor_id');
    }

    public function floorPartitions()
    {
        return $this->belongsTo(FloorPartition::class, 'floor_partition_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_id');
    }
    public function waiterRequests()
    {
        return $this->hasMany(WaiterRequest::class, 'table_id');
    }
    public function branches()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
