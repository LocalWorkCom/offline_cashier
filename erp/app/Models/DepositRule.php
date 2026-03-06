<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class DepositRule extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'deposit_rules';


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('deposit_rules');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $fillable = [
        'name_ar',
        'name_en',
        'percentage',
        'applicable_to',
        'vendor_type',
        'conditions',
        'active',
        'linked_high_value_rule_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'percentage' => 'integer',
        'active' => 'boolean',
        'conditions' => 'array', // JSON field
    ];
    protected $appends = ['name'];
    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }
    // Relations
    public function highValueRule()
    {
        return $this->belongsTo(HighValueRule::class, 'linked_high_value_rule_id');
    }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'deposit_rule_vendor')
            ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(Employee::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(Employee::class, 'deleted_by');
    }
}
