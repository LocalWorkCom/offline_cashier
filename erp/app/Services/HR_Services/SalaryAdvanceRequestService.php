<?php

namespace App\Services\HR_Services;

use App\Models\SalaryAdvanceRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SalaryAdvanceRequestService
{
    // Create a new Salary Advance Request
    public function create(array $data)
    {
        return SalaryAdvanceRequest::create($data);
    }

    // Get all Salary Advance Requests
    public function getAll()
    {
        return SalaryAdvanceRequest::query();
    }

    // Get a single Salary Advance Request by ID
    public function getById($id)
    {
        $salaryAdvanceRequest = SalaryAdvanceRequest::find($id);

        if (!$salaryAdvanceRequest) {
            return CustomRespondWithBadRequest(__('validation.NotFound'));
        }

        return $salaryAdvanceRequest;
    }

    // Update an existing Salary Advance Request
    public function update($id, array $data)
    {
        $salaryAdvanceRequest = SalaryAdvanceRequest::find($id);

        if (!$salaryAdvanceRequest) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        $salaryAdvanceRequest->update($data);

        return $salaryAdvanceRequest;
    }

    // Delete a Salary Advance Request
    public function delete($id)
    {
        $salaryAdvanceRequest = SalaryAdvanceRequest::find($id);

        if (!$salaryAdvanceRequest) {
            return CustomRespondWithBadRequest(__('validation.NotFound'));
        }

        $salaryAdvanceRequest->delete();

        return true;
    }
}
