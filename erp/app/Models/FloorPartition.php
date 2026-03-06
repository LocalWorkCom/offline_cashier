<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class FloorPartition extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('floor-partition');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $appends = ['name', 'exist_table', 'name_site'];

    protected $hidden = ['created_by', 'modified_by', 'deleted_by', 'deleted_at', 'updated_at', 'created_at'];

    public function getNameAttribute($value){
        return Request()->header('lang') == "en" ? $this->name_en : $this->name_ar;
    }

    public function getExistTableAttribute($value){
        return $this->tables->count();
    }

    public function getNameSiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_ar;
    }

    public function tables()
    {
        return $this->hasMany(Table::class, 'floor_partition_id', 'id');
    }

    public function floors()
    {
        return $this->belongsTo(Floor::class, 'floor_id', 'id');
    }

    public function branches()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

}
