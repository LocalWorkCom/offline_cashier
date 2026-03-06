<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class FiledOfStudy extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('filed-of-study');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $table = 'filed_of_studies'; // If your table is called 'employee_status' (no "es")

    protected $appends = ['name'];

    protected $fillable = [
        'name_en',
        'name_ar',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    protected $hidden = [
        'name_en',
        'name_ar',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
        "created_by_type",
        "deleted_by_type",
        "modified_by_type"
    ];

    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }

    // EthnicBackground.php
    public function employees()
    {
        return $this->hasMany(Employee::class, 'filed_of_study_id');
    }
}
