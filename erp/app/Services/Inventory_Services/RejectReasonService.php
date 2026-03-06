<?php

namespace App\Services\Inventory_Services;

use App\Models\RejectReason;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RejectReasonService
{
    /**
     * Get all reject reasons (with optional trashed).
     */
    public function all($withTrashed = false)
    {
        $query = RejectReason::query()->orderBy('id', 'desc');

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query;
    }

    /**
     * Store a new reject reason.
     */
    public function store(array $data)
    {
        $this->validate($data);

        $data['created_by'] =  auth('employee')->id();

        return RejectReason::create($data);
    }

    /**
     * Update an existing reject reason.
     */
    public function update($id, array $data)
    {
        $reason = RejectReason::find($id);
        $this->validate($data);

        $data['updated_by'] =  auth('employee')->id();
        $reason->update($data);

        return $reason;
    }

    /**
     * Soft delete a reject reason.
     */
    public function destroy($id)
    {
        $reason = RejectReason::find($id);

        $reason->deleted_by = auth('employee')->id();
        $reason->save();
        $reason->delete();

        return true;
    }

    /**
     * Restore a soft-deleted record.
     */
    public function restore($id)
    {
        $reason = RejectReason::onlyTrashed()->find($id);
        $reason->restore();

        return $reason;
    }

    /**
     * Validation rules.
     */
    protected function validate(array $data)
    {
        $validator = Validator::make($data, [
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
