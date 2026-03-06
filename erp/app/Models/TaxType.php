<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxType extends Model
{
    use HasFactory;
    protected $fillable = ['name_en','name_ar', 'percentage', 'is_default','is_active', 'created_by', 'updated_by'];
}


