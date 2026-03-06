<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvanceRequest extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = [
        'advance_setting_id',
        'employee_id',
        'reason',
        'status',
        'created_by',
        'modified_by',
        'deleted_by',

    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    public function setting(){
        return $this->belongsTo(AdvanceSetting::class,'advance_setting_id','id');
    }
    public function employee(){
        return $this->belongsTo(Employee::class,'employee_id','id')->withTrashed();
    }
}
