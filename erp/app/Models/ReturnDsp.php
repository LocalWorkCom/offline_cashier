<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class ReturnDsp extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('reason-list');
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
        'dsp_id',
        'reason_id',
        'returned_quantity',
        'status',
        'created_by',
        'created_by_type',
        'modified_by',
        'modified_by_type',
        'deleted_by',
        'deleted_by_type',
        'approved_by',
        'approved_by_type',
        'rejected_by',
        'rejected_by_type',
        'submitted_by',
        'submitted_by_type',
        'approved_at',
        'rejected_at',
        'submitted_at',
    ];
    protected $dates = ['deleted_at'];

    protected $hidden = [
        'created_by',
        'created_by_type',
        'modified_by',
        'modified_by_type',
        'deleted_by',
        'deleted_by_type',
        'approved_by',
        'approved_by_type',
        'rejected_by',
        'rejected_by_type',
        'submitted_by',
        'submitted_by_type',
        'deleted_at',
        'created_at',
        'updated_at',
        'approved_at',
        'rejected_at',
        'submitted_at',
    ];
    protected $casts = [
        'items' => 'array',
        'quantity' => 'array',
    ];
    public function reason()
    {
        return $this->belongsTo(ReasoneReturnDsp::class, 'reason_id');
    }
    public function documents()
    {
        return $this->hasMany(DocumentReturnDsp::class, 'return_dsp_id');
    }
    public function dsp()
    {
        return $this->belongsTo(DirectSupplyPermission::class, 'dsp_id');
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function modifier()
    {
        return $this->belongsTo(Employee::class, 'modified_by');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function rejector()
    {
        return $this->belongsTo(Employee::class, 'rejected_by');
    }

    public function submitter()
    {
        return $this->belongsTo(Employee::class, 'submitted_by');
    }

    public function deleter()
    {
        return $this->belongsTo(Employee::class, 'deleted_by');
    }


    public function items()
    {
        return $this->hasMany(DspItemIssue::class, 'dsp_item_id', 'id');
    }
    public function dspItems()
{
    return $this->hasManyThrough(
        DirectSupplyPermissionItem::class,
        DirectSupplyPermission::class,
        'id',        // Foreign key on DirectSupplyPermission (ReturnDsp.dsp_id -> DirectSupplyPermission.id)
        'dsp_id',    // Foreign key on DirectSupplyPermissionItem
        'dsp_id',    // Local key on ReturnDsp
        'id'         // Local key on DirectSupplyPermission
    );
}

}
