<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActionBacklog extends Model
{
    use HasFactory ,SoftDeletes;

    protected $hidden = [
        'created_by',
        'created_at',
        'deleted_at',
        'updated_at',
    ];

}
