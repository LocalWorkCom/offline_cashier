<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyPolicyAcknowledgement extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_policy_id',
        'employee_id',
        'viewed_at',
    ];
    public $timestamps = false;

    public function companyPolicy()
    {
        return $this->belongsTo(CompanyPolicy::class, 'company_policy_id');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
