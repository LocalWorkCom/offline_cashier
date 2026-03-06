<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HRRequest extends Model
{
    use HasFactory;
    protected  $table = 'hr_requests';
    protected $appends = ['request_object'];
    protected $hidden = ['hr_service'];

    protected $fillable = [
        'employee_id',
        'hr_service_id',
        'request_id',
        'status',
        'created_by',
        'created_by_type',
        'updated_by',
        'updated_by_type',
        'deleted_by',
        'deleted_by_type'
    ];

    // Relationship with User (Employee)
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function getRequestObjectAttribute()
    {
        if (!$this->hrService) {
            return null;
        }

        return match ($this->hrService->key) {
            'LeaveRequest' => \App\Models\LeaveRequest::with('leaveTypes')->find($this->request_id),
            'SalaryAdvance' => \App\Models\SalaryAdvanceRequest::find($this->request_id),
            default => null,
        };
    }



    public function hrService()
    {
        return $this->belongsTo(HRService::class);
    }
}
