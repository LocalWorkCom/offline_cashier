<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceEvent extends Model
{
    use HasFactory;
    protected $fillable = [
        'department_id',
        'type_alerts_id',
        'employee_ids',
        'type_notification_id',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    public function typeAlert()
    {
        return $this->belongsTo(TypeAlert::class, 'type_alerts_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function typeNotification()
    {
        return $this->belongsTo(TypeMessageActive::class, 'type_notification_id');
    }
}
