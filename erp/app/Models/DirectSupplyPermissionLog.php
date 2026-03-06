<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirectSupplyPermissionLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'direct_supply_permission_logs';
    protected $fillable = [
        'dsp_id',
        'action',
        'details',
        'employee_id',
    ];

    public function DirectSupplyPermission()
    {
        return $this->belongsTo(DirectSupplyPermission::class, 'dsp_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
