<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiometricTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'emp_code',
        'first_name',
        'last_name',
        'department',
        'position',
        'punch_time',
        'punch_state',
        'punch_state_display',
        'verify_type',
        'verify_type_display',
        'work_code',
        'gps_location',
        'area_alias',
        'terminal_sn',
        'temperature',
        'terminal_alias',
        'upload_time',
    ];

    protected $casts = [
        'punch_time' => 'datetime',
        'upload_time' => 'datetime',
        'temperature' => 'float',
    ];
}
