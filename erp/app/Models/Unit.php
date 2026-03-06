<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Unit extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('unit');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    public function dspItems()
    {
        return $this->hasMany(DirectSupplyPermissionItem::class, 'unit_id');
    }


    // The table associated with the model.
    protected $table = 'units';

    protected $appends = ['name', 'description'];

    // The attributes that are mass assignable.
    protected $fillable = [
        'name_ar',
        'name_en',
        'active',
        'description_ar',
        'description_en',
        'abbreviation'

    ];

    protected $hidden = [
        'created_type',
        'deleted_type',
        'modified_type',
        'created_by',
        'modify_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'name_ar',
        'name_en',

    ];
    protected $casts = [
        'active' => 'boolean',
    ];
    public function creatorby()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function getNameAttribute($value)
    {
        return Request()->header('lang') == "en" ? $this->name_en : $this->name_ar;
    }
    public function getDescriptionAttribute($value)
    {
        return Request()->header('lang') == "en" ? $this->description_en : $this->description_ar;
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('unit_id', 'factor');
    }
    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }
    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(Employee::class, 'modify_by');
    }
    // Optionally, you can define other model properties or methods here
}
