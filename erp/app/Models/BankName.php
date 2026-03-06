<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class BankName extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('bank-name');
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

    protected $fillable = [
        'name_en',
        'name_ar',
        'logo',
        'created_by',
        'modify_by',
        'deleted_by',
    ];

    protected $hidden = [
        'created_by',
        'modify_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }
        public function employeeBankingInfo()
    {
        return $this->hasMany(EmployeeBankingInfo::class, 'bank_name_id');
    }
    // In BankName model
public function employees()
{
    return $this->hasManyThrough(
        Employee::class,
        EmployeeBankingInfo::class,
        'bank_name_id', // Foreign key on employee_banking_info table
        'id', // Foreign key on employees table
        'id', // Local key on bank_names table
        'employee_id' // Local key on employee_banking_info table
    );
}
}
