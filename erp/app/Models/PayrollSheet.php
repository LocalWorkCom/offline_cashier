<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayrollSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_date',
        'end_date',
        'status',
        'comments',
        'type', // 'monthly' or 'daily'
    ];

    public function items()
    {
        return $this->hasMany(PayrollSheetItem::class);
    }
}
