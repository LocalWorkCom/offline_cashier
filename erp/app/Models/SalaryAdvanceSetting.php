<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryAdvanceSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'percentage_type',
        'max_percentage',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
    ];
    protected $hidden = [
        'created_by',
        'modified_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];
}
