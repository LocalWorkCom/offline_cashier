<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViolationLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'violation_id',
        'old_penalty_id',
        'new_penalty_id',
        'old_order',
        'new_order',
        'action',
        'changed_by'
    ];
}
