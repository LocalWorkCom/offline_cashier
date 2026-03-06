<?php

namespace App\Services\HR_Services;

use App\Models\DocumentType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DocumentTypeService
{
    /**
     * Get all document types.
     */
    public function getAll()
    {
        return DocumentType::query();
    }

    /**
     * Create a new document type.
     */
    public function create(array $data)
    {
        $user = auth('employee')->user();

        return DocumentType::create([
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'],
            'created_by' => $user->id
        ]);
    }
}
