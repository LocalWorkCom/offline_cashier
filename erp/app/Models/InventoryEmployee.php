<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class InventoryEmployee extends Model
{
  use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('addon-category');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $table = 'inventory_employees';
    protected $fillable = [
        'employee_id',
        'store_id',
        'position',
        'department',
    ];

     public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Each record belongs to a store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
