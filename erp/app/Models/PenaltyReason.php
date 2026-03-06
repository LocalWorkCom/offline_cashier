<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class PenaltyReason extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('penalty-reason');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $appends = ['name', 'reason'];
    protected $fillable = [
        'reason_ar',
        'reason_en',
        'punishment_ar',
        'punishment_en',
        'note',
        'code',
        'type',
        'requires_approval',
        'created_by',
        'modified_by',
        'deleted_by'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'modified_by',
        'deleted_by',
    ];
    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->reason_en : $this->reason_ar;
    }
    public function getReasonAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->reason_en : $this->reason_ar;
    }
    public function penalties()
    {
        return $this->hasMany(Penalty::class, 'reason_id');
    }
}
