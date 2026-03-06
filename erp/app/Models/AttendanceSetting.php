<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'deduction_type',
        'threshold_minutes',
        'round_unit',
        'allow_manual_override',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'allow_manual_override' => 'boolean',
    ];
}
