<?php

namespace App\Models;

use Google\Service\CloudCommercePartnerProcurementService\Approval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class Penalty extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('penalty');
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
        'reason_id',
        'employee_id',
        'note',
        'created_by',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    public function reason(){
        return $this->belongsTo(PenaltyReason::class, 'reason_id');
    }
    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    public function penaltyDeductions(){
        return $this->hasMany(PenaltyDeduction::class, 'penalty_id');
    }
    public function approval(){
        return $this->hasOne(PenaltyApproval::class, 'penalty_id');
    }
    public function documents(){
        return $this->hasMany(PenaltyDocument::class, 'penalty_id');
    }

}
