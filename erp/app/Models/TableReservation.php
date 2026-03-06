<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class TableReservation extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('table-reservation');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $table = 'table_reservations';
    protected $fillable = [
        'branch_id',
        'floor_partition_id',
        'table_id',
        'client_id',
        'time_from',
        'time_to',
        'confirmed_time',
        'date',
        'status',
        'order_id',
    ];

    protected $casts = [
        'time_from' => 'datetime',
        'time_to' => 'datetime',
        'confirmed_time' => 'datetime',
        'date' => 'datetime',
    ];

    protected $hidden = ['created_by', 'modified_by', 'deleted_by', 'deleted_at', 'updated_at', 'created_at'];

    public function tables()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function floorPartition()
    {
        return $this->belongsTo(FloorPartition::class, 'floor_partition_id');
    }
    public function transaction()
    {
        return $this->hasOne(TableReservationTransaction::class, 'table_reservation_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
