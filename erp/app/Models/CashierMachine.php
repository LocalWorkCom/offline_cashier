<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class CashierMachine extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('cashier-machine');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $appends = ['name'];

    protected $hidden = [
        'name_ar',
        'name_en',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
        'deleted_at',
        'updated_at',
        'created_at'
    ];

    public function getNameAttribute($value)
    {
        return Request()->header('lang') == "en" ? $this->name_en : $this->name_ar;
    }

    public function branches()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
    // Relationship to CashierSettings
    public function cashierSettings()
    {
        return $this->hasMany(CashierSetting::class, 'pos_id');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_machines', 'cashier_machine_id', 'employee_id');
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
}
