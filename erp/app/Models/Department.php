<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Department extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('department');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $appends = ['name', 'description'];

    protected $fillable = [
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'parent_id',
        'branch_id',
        'created_by',
        'branch_id',
        'modified_by',
        'deleted_by',
        'status'

    ];

    protected $hidden = [
        // 'name_en',
        // 'name_ar',
        // 'description_en',
        // 'description_ar',
        'created_by',
        'modified_by',
        'deleted_by',

        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }

    public function getDescriptionAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->description_en : $this->description_ar;
    }

    // Relationships
    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
    // In App\Models\Department.php

    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function subDepartments()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }
}
