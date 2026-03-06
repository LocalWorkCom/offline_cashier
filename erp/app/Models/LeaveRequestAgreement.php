<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequestAgreement extends Model
{
    use HasFactory;

    protected $fillable = ['leave_request_id', 'agreement_by', 'agreement', 'created_by', 'resone', 'position_id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'agreement_by');
    }

}
