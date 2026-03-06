<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advance extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = [
        'request_id',
        'employee_id',
        'approval_date',
        'starting_date',
        'ending_date',
        'amount_per_month',
        'note',
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

    public function request(){
        return $this->belongsTo(AdvanceRequest::class,'request_id','id');
    }
    public function employee(){
        return $this->belongsTo(Employee::class,'employee_id','id')->withTrashed();
    }
}
