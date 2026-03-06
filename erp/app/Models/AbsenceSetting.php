<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsenceSetting extends Model
{
    protected $table = 'absence_settings';

    protected $fillable = [
        			
        'penalty_mode',          // e.g. 'late', 'early_departure', 'absence'
        'deduct_from',  // e.g. 'fixed', 'percentage'
        'allow_manual_override', // numeric value of penalty
    ];
}
