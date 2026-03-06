<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderSetting extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = [
        'tax_application',
        'coupon_application',
        'tax_percentage',
        'time_cancellation',
        'delivery_time',
        'delivery_difference'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'modified_by',
        'deleted_by',
    ];
}
