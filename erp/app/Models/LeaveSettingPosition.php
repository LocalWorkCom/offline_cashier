<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Position;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class LeaveSettingPosition extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('leave-setting-position');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $table = 'leave_setting_positions';
    protected $appends = ['higher_position_approve_name', 'roles_assign_name', 'roles_view_name'];

    protected $hidden = [
        'is_active',
        'created_by',
        'created_by_type',
        'modified_by',
        'modified_by_type',
        'deleted_by',
        'deleted_by_type',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public function getHigherPositionApproveNameAttribute()
    {
        $ids = json_decode($this->higher_position_approve);
        if (is_array($ids) && count($ids)) {
            $filed_name = app()->getLocale() === 'en' ? "name_en" : "name_ar";
            return Position::whereIn('id', $ids)->pluck($filed_name)->implode(',');
        }
        return "";
    }

    public function getRolesAssignNameAttribute()
    {
        $ids = json_decode($this->roles_assign);
        if (is_array($ids) && count($ids)) {
            return DB::table('roles')->whereIn('id', $ids)->pluck("name")->implode(',');
        }
        return "";
    }

    public function getRolesViewNameAttribute()
    {
        $ids = json_decode($this->roles_view);
        if (is_array($ids) && count($ids)) {
            return DB::table('roles')->whereIn('id', $ids)->pluck("name")->implode(',');
        }
        return "";
    }

    public function leaveSettings()
    {
        return $this->belongsTo(LeaveSetting::class, 'leave_setting_id', 'id');
    }

    public function positions()
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }
}
