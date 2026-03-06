<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\EmployeeSchedule;
use App\Services\HR_Services\EmployeeScheduleService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EmployeeScheduleController extends Controller
{
    protected $employeeScheduleService;

    public function __construct(EmployeeScheduleService $employeeScheduleService)
    {
        $this->employeeScheduleService = $employeeScheduleService;
    }
    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            App::setLocale($lang);

            $schedules = $this->employeeScheduleService->index();
            $response = paginateOrGetAll($schedules, $request, ['']);
            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching schedules: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            App::setLocale($lang);

            $schedule = $this->employeeScheduleService->show($id, $lang);
            if ($schedule['status'] === 'error') {
                return respondError($schedule['message'], 404);
            }
            return ResponseWithSuccessData($lang, $schedule, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching schedule: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function store(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            App::setLocale($lang);

            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'shift_id' => 'required|exists:shifts,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            if ($validator->fails()) {
                return respondError($validator->errors(), 400);
            }

            $schedule = $this->employeeScheduleService->store($request);

            return ResponseWithSuccessData($lang, $schedule, 1);
        } catch (\Exception $e) {
            Log::error('Error creating employee schedule: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function setDefault(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            App::setLocale($lang);

            $validator = Validator::make($request->all(), [
                'branches' => 'nullable|array',
                'branches.*' => 'exists:branches,id',
                'departments' => 'nullable|array',
                'departments.*' => 'exists:departments,id',
                'positions' => 'nullable|array',
                'positions.*' => 'exists:positions,id',
                'employees' => 'nullable|array',
                'employees.*' => 'exists:employees,id',
                'shift_id' => 'required|exists:shifts,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            if ($validator->fails()) {
                return respondError($validator->errors(), 400);
            }

            //Collect filters
            $branches = $request->input('branches');
            $departments = $request->input('departments');
            $positions = $request->input('positions');
            $employees = $request->input('employees');

            //If all are empty → apply to all active employees
            $applyToAll = (empty($branches) && empty($departments) && empty($positions) && empty($employees));
            if ($applyToAll) {
                $allEmployees = Employee::whereNull('deleted_at')
                    ->where('status', 'active')
                    ->pluck('id')
                    ->toArray();
                $request->merge(['employees' => $allEmployees]);
            }

            // Call the service
            $schedule = $this->employeeScheduleService->setDefault($request->all());

            //Handle failure (no employees found)
            if ($schedule['status'] === 'false') {
                return respondError(
                    __('employee.no_employees_found_for_filters'),
                    404
                );
            }

            // Success response
            return ResponseWithSuccessData($lang, $schedule['message'], 1);
        } catch (\Exception $e) {
            Log::error('Error creating employee schedule: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            App::setLocale($lang);

            $schedule = EmployeeSchedule::find($id);
            if (!$schedule) {
                $message = $lang == 'en' ? 'Schedule not found' : 'جدول العمل غير موجود';
                return respondError($message, 404);
            }

            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:employees,id',
                'shift_id' => 'required|exists:shifts,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            if ($validator->fails()) {
                return respondError($validator->errors(), 400);
            }

            $schedule = $this->employeeScheduleService->update($request, $id, $lang);

            return ResponseWithSuccessData($lang, $schedule, 1);
        } catch (\Exception $e) {
            Log::error('Error updating employee schedule: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function destroy(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            App::setLocale($lang);

            $schedule = EmployeeSchedule::find($id);
            if (!$schedule) {
                $message = $lang == 'en' ? 'Schedule not found' : 'جدول العمل غير موجود';
                return respondError($message, 404);
            }
            $schedule = $this->employeeScheduleService->delete($id);
            if ($schedule['status'] === 'error') {
                return respondError($lang == 'ar' ? 'لا يمكن حذف الجدول أثناء فترة نشاطه.' : 'Schedule cannot be deleted during its active period', 400);
            }
        $message = $schedule['flag'] 
            ? ($lang == 'ar' 
                ? 'تم حذف جدول الموظف لأن بداية الشيفت لم تبدأ بعد ولم يصبح له أي شيفت' 
                : 'The employee schedule was deleted because the shift has not started yet and no shifts have been assigned.') 
            : null;

            return ResponseWithSuccessData($lang, $message, 1);
        } catch (\Exception $e) {
            Log::error('Error deleting employee schedule: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function restore(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            App::setLocale($lang);

            $schedule = EmployeeSchedule::onlyTrashed()->findOrFail($id);
            $schedule->restore();

            return ResponseWithSuccessData($lang, $schedule->load('employee', 'shift.details.timetable'), 1);
        } catch (\Exception $e) {
            Log::error('Error restoring employee schedule: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function getEmployeeSchedules(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            App::setLocale($lang);

            $employeeId = auth('employee')->user()->id;


            $schedules = getEmployeeWorkSchedule($employeeId, $lang);

            return ResponseWithSuccessData($lang, $schedules, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching employee schedules: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
