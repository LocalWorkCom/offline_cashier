<?php

namespace App\Traits;

use App\Models\Employee;

trait HasAuditTrail
{
   public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function modifier()
    {
        return $this->belongsTo(Employee::class, 'modified_by');
    }

    public function deleter()
    {
        return $this->belongsTo(Employee::class, 'deleted_by');
    }
}
