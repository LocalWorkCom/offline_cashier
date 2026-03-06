<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class Complaint extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('complaint');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $fillable =
        [
            'client_id',
            'order_id',
            'rate',
            'complain',
            'comment',
            'manage',
            'status',
            'created_by',
            'modified_by',
            'deleted_by',
        ];

    protected $hidden =
        [
            'created_at',
            'updated_at',
            'deleted_at',
            'created_by',
            'modified_by',
            'deleted_by',
        ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id', 'id')->where('flag', 'client');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id','id');
    }
}
