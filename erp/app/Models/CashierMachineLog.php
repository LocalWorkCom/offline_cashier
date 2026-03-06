<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashierMachineLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'cashier_machine_id',
        'employee_opening_balance_id',
        'open_cash',
        'open_visa',
        'close_cash',
        'close_visa',
        'real_cash',
        'real_visa',
        'deficit_cash',
        'deficit_visa',
        'deficit_cash_close',
        'deficit_visa_close',
        'type',
        'time',
        'date',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    protected $hidden = [
            'created_by',
            'modified_by',
            'deleted_by',
            'deleted_at',
            'updated_at',
            'created_at'
        ];

}
