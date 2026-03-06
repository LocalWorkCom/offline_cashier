<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\HRRequest;
use App\Services\HR_Services\HRRequestService;
use Illuminate\Http\Request;

class HRRequestController extends Controller
{
    protected $hrRequestService;
    protected $lang;

    public function __construct(HRRequestService $hrRequestService)
    {
        $this->hrRequestService = $hrRequestService;
        $this->lang = app()->getLocale(); // Store current language for responses
    }


    public function index(Request $request)
    {
        $employee = auth('employee')->user();
        $lang = $request->header('lang', 'en');


        // $filters = $employee->hasRole('HR_Manager')
        //     ? $request->only(['employee_id', 'hr_service_id', 'request_id'])
        //     : ['employee_id' => $employee->id];
        $filters = $request->only(['status', 'hr_service_id', 'request_id', 'from', 'to', 'search']);


        $leave_requests = $this->hrRequestService->getAll($filters);
        $child_employees = getSupervisedEmployees($employee->id);

        if ($employee->hasRole('HR_Manager')) {

            $myRequests = (clone $leave_requests)->where('employee_id', $employee->id);
            $employeeRequests = (clone $leave_requests)->where('employee_id', '!=', $employee->id);
        } elseif ($child_employees && $child_employees->count() > 0) {

            $childIds = $child_employees->pluck('id')->toArray();
            $myRequests = (clone $leave_requests)->where('employee_id', $employee->id);
            $employeeRequests = (clone $leave_requests)->whereIn('employee_id', $childIds);
        } else {

            $myRequests = (clone $leave_requests)->where('employee_id', $employee->id);
            $employeeRequests = HRRequest::query()->whereRaw('1 = 0');
        }

        $myRequestsData = paginateOrGetAll($myRequests, $request, []);
        $employeeRequestsData = paginateOrGetAll($employeeRequests, $request, []);
        if ($employeeRequestsData['data']) {
            $employeeRequestsData['data']->transform(function ($item) {

                $totalHolidayCount = null;
                if ($item->employee && $item->employee->leaveTypes_employees) {
                    foreach ($item->employee->leaveTypes_employees as $holiday) {
                        $totalHolidayCount += $holiday->pivot->day_count - $holiday->pivot->day_paid;
                    }
                }

                $item->employee->holidayCount = $totalHolidayCount;
                if ($item->employee) {
                    $item->employee->makeHidden(['kitchen_info']);
                }

                return $item;
            });
        }
        if ($myRequestsData['data']) {
            $myRequestsData['data']->transform(function ($item) {

                $totalHolidayCount = null;
                if ($item->employee && $item->employee->leaveTypes_employees) {
                    foreach ($item->employee->leaveTypes_employees as $holiday) {
                        $totalHolidayCount += $holiday->pivot->day_count - $holiday->pivot->day_paid;
                    }
                }

                $item->employee->holidayCount = $totalHolidayCount;
                if ($item->employee) {
                    $item->employee->makeHidden(['kitchen_info']);
                }

                return $item;
            });
        }

        return ResponseWithSuccessData($lang, [
            'my' => $myRequestsData['data'] ?? null,
            'employees' => $employeeRequestsData['data'] ?? null,
        ], 1);
        // $data = paginateOrGetAll($hrRequests, $request, []);

        // return ResponseWithSuccessDataPaginated($this->lang, $data, 1);
    }

    // Get a single HR request
    public function show($id)
    {
        $employee = auth('employee')->user();

        if (!$employee->hasRole('HR_Manager')) {
            return RespondWithBadRequest($this->lang, 2);
        }
        try {
            $hrRequest = $this->hrRequestService->getById($id);
            return ResponseWithSuccessData($this->lang, $hrRequest, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequest($this->lang, 2);
        }
    }

    // Create a new HR request
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id', // Assuming `users` table for employees
            'hr_service_id' => 'required|exists:hr_services,id', // Assuming `hr_services` table
            'request_id' => 'required|integer',
            'status' => 'required|in:pending,approved,rejected',
        ]);
        $validated['created_by'] =  authActionSave()['by'];
        $validated['created_by_type'] = authActionSave()['type'];
        try {
            $hrRequest = $this->hrRequestService->create($validated);
            return RespondWithBadRequestWithData($hrRequest);
        } catch (\Exception $e) {
            return RespondWithBadRequest($this->lang, 2);
        }
    }


    // Update an existing HR request
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'hr_service_id' => 'required|exists:hr_services,id',
            'request_id' => 'required|integer',
            'status' => 'required|in:pending,approved,rejected',
        ]);
        $validated['updated_by'] =  authActionSave()['by'];
        $validated['updated_by_type'] = authActionSave()['type'];
        try {
            $hrRequest = $this->hrRequestService->update($id, $validated);
            return RespondWithBadRequestWithData($hrRequest);
        } catch (\Exception $e) {
            return RespondWithBadRequest($this->lang, 2);
        }
    }

    // Delete an HR request
    public function destroy($id)
    {
        try {
            $this->hrRequestService->delete($id);
            return RespondWithSuccessRequest($this->lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequest($this->lang, 2);
        }
    }
}
