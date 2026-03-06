<?php

namespace App\Services\HR_Services;

use App\Models\HRRequest;
use App\Models\HRService;

class HRRequestService
{
    // Create a new HR request
    public function create(array $data)
    {
        return HRRequest::create($data);
    }

    // Get all HR requests with optional filters
    public function getAll(array $filters = [])
    {
        $query = HRRequest::with(['employee', 'hrService', 'employee.position', 'employee.leaveTypes_employees'])->orderBy('created_at', 'desc');

        // if (!empty($filters['employee_id'])) {
        //     $query->where('employee_id', $filters['employee_id']);
        // }

        if (!empty($filters['status'])) {

            $query->whereIn('request_id', function ($q) use ($filters) {
                $q->select('id')
                    ->from('leave_requests')
                    ->where('status', $filters['status']);
            });
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query->whereIn('request_id', function ($q) use ($filters) {
                $q->select('id')
                    ->from('leave_requests')
                    ->whereBetween('date', [$filters['from'], $filters['to']]);
            });
        }

        if (!empty($filters['search'])) {
            $query->whereIn('request_id', function ($q) use ($filters) {
                $q->select('id')
                    ->from('leave_requests')
                    ->where('date', $filters['search']);
            });
        }


        if (!empty($filters['hr_service_id'])) {
            $query->where('hr_service_id', $filters['hr_service_id']);
        }

        if (!empty($filters['request_id'])) {
            $query->where('request_id', $filters['request_id']);
        }


        return $query;
    }


    // Get a single HR request by ID
    public function getById($id)
    {
        $hrRequest = HRRequest::with(['employee', 'hrService', 'request'])->find($id);

        if (!$hrRequest) {
            return CustomRespondWithBadRequest(__('validation.NotFound'));
        }

        return $hrRequest;
    }

    // Update an existing HR request
    public function update($id, array $data)
    {
        $hrRequest = HRRequest::find($id);

        if (!$hrRequest) {
            return CustomRespondWithBadRequest(__('validation.NotFound'));
        }

        $hrRequest->update($data);

        return $hrRequest;
    }

    // Delete an HR request
    public function delete($id)
    {
        $hrRequest = HRRequest::find($id);

        if (!$hrRequest) {
            return CustomRespondWithBadRequest(__('validation.NotFound'));
        }

        $hrRequest->delete();

        return true;
    }
    public  function getByRequestId($request_id, $hr_service_id, $employee_id)
    {
        $hrRequest = HrRequest::where('request_id', $request_id)->where('hr_service_id', $hr_service_id)->where('employee_id', $employee_id)->first();
        return $hrRequest;
    }
}
