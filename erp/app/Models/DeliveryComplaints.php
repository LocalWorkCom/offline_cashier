<?php
namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryComplaints extends Model
{
       use SoftDeletes ,HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('delivery-complaints');
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
        'order_id',
        'employee_id',
        'message',
        'status',
        'manage',
        'reason_id',
        'latitude',
        'longitude',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public const STATUS_HOLD = 'hold';
    public const STATUS_DONE = 'done';

    public static function getStatuses()
    {
        return [
            self::STATUS_HOLD,
            self::STATUS_DONE,
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class,'order_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
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
