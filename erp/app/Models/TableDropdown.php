<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableDropdown extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'source_of_table',
    ];
}
