<?php

namespace App\Services\HR_Services;

use App\Models\EmployeeDocument;
use App\Models\EmployeeDocumentManagement;
use Illuminate\Support\Facades\Auth;

class EmployeeDocumentService
{
    /**
     * Get all employee documents.
     */
    public function getAll(array $filters = [])
    {
        $query = EmployeeDocumentManagement::with('documentType');
        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['document_type_id'])) {
            $query->where('document_type_id', $filters['document_type_id']);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        // return $query->get();
        return $query;
    }

    /**
     * Create a new employee document.
     */
    public function create(array $data)
    {
 
        // $user = auth('employee')->user();

         return EmployeeDocumentManagement::create([
            'document_type_id' => $data['document_type_id'],
            'employee_id' => $data['employee_id'],
            'date' => $data['date'],
            'file'             => null,
            'created_by' => 3
        ]);
    }
}
