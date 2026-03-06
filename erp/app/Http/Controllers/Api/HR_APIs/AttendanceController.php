<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Services\HR_Services\EmployeeAttendanceService;
use App\Services\HR_Services\TimetableService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    protected $attendanceService;
    protected $timeTableService;

    public function __construct(EmployeeAttendanceService $attendanceService, TimetableService $timeTableService)
    {
        $this->attendanceService = $attendanceService;
        $this->timeTableService = $timeTableService;
    }

    /**
     * Get my attendance (for authenticated user)
     */
    public function getMyAttendance(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2020',
            'status' => 'nullable|in:present,absent,late,overtime,early_departure',
        ]);

        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }

        $filters = $request->only(['start_date', 'end_date', 'month', 'year', 'status']);
        $result = $this->attendanceService->getMyAttendance($filters, $lang);

        if (isset($result['error']) && $result['error']) {
            return respondError($result['message'], 404);
        }

        return ResponseWithSuccessData($lang, $result['data'], 1);
    }

    /**
     * Get attendance by employee ID
     */
    public function getAttendanceByEmployee(Request $request, $employeeId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make(array_merge($request->all()), [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2020',
            'status' => 'nullable|in:present,absent,late,overtime,early_departure',
        ]);

        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }

        $filters = $request->only(['start_date', 'end_date', 'month', 'year', 'status']);
        $result = $this->attendanceService->getAttendanceByEmployeeId($employeeId, $filters, $lang);

        if (isset($result['error']) && $result['error']) {
            return respondError($result['message'], 404);
        }

        return ResponseWithSuccessData($lang, $result['data'], 1);
    }

    /**
     * Get all employees attendance with filters
     */
    public function getAllAttendance(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|min:2020',
            'status' => 'nullable|in:present,absent,late,overtime,early_departure',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'integer|exists:employees,id',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'integer|exists:departments,id',
            'branch_ids' => 'nullable|array',
            'branch_ids.*' => 'integer|exists:branches,id',
            'position_ids' => 'nullable|array',
            'position_ids.*' => 'integer|exists:positions,id',
        ]);

        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }

        $filters = $request->only([
            'start_date',
            'end_date',
            'month',
            'year',
            'status',
            'employee_ids',
            'department_ids',
            'branch_ids',
            'position_ids',
            'search',
        ]);

        $result = $this->attendanceService->getAllAttendance($filters);

        return ResponseWithSuccessData($lang, $result['data'], 1);
    }

    /**
     * Get attendance summary/statistics
     */
    public function getAttendanceSummary(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'employee_id' => 'nullable|integer|exists:employees,id',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }

        $filters = $request->only(['start_date', 'end_date']);
        $result = $this->attendanceService->getAttendanceSummary($request->employee_id, $filters);

        return ResponseWithSuccessData($lang, $result['data'], 1);
    }

    /**
     * Get my attendance summary
     */
    public function getMyAttendanceSummary(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }

        $user = Auth::user();
        $employee = Employee::where('id', $user->id)->first();

        if (!$employee) {
            return respondError('Employee not found', 404);
        }

        $filters = $request->only(['start_date', 'end_date']);
        $result = $this->attendanceService->getAttendanceSummary($employee->id, $filters);

        return ResponseWithSuccessData($lang, $result['data'], 1);
    }

    /**
     * Get daily attendance report
     */
    public function getDailyAttendanceReport(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
            'branch_ids' => 'nullable|array',
            'branch_ids.*' => 'integer|exists:branches,id',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'integer|exists:departments,id',
            'position_ids' => 'nullable|array',
            'position_ids.*' => 'integer|exists:positions,id',
        ]);

        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }

        $filters = $request->only(['branch_ids', 'department_ids', 'position_ids']);
        $result = $this->attendanceService->getDailyAttendanceReport($request->date, $filters);

        return ResponseWithSuccessData($lang, $result['data'], 1);
    }

    public function checkAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer|exists:employees,id',
            'date' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $employee = Employee::find($request->employee_id);
        $date = Carbon::parse($request->date);

        $attendance = checkEmployeeAttendance($employee, $date);
        $attendanceStatus = checkEmployeeAttendanceStatus($employee, $date);

        return response()->json([
            'status' => 'success',
            'employee_id' => $employee->id,
            'employee_code' => $employee->employee_code,
            'date' => $date->toDateString(),
            'attendance' => $attendance,
            'detailed_status' => $attendanceStatus,
        ], 200);
    }
    public  function generateRangeReport(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $from_date = $request->from_date ?? date('Y-m-d');
        $to_date = $request->to_date ?? date('Y-m-d');
        $result =  $this->attendanceService->generateRangeReport($from_date, $to_date);
        return ResponseWithSuccessData($lang, $result, 1);
    }

    public function lastActivity(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        // $result = $this->attendanceService->lastActivity($request->employee_id);

        $employee = auth('employee')->user();
        $employee = Employee::find($employee->id);

        if (!$employee) {
            return [
                'error' => true,
                'message' => $lang == 'en' ? 'Employee not found' : 'الموظف غير موجود',
                'data' => []
            ];
        }

        $lastClockIn = Attendance::where('employee_id', $employee->id)
            ->whereNotNull('clock_in_time')
            ->orderBy('id', 'desc')
            ->first();

        $lastClockout = Attendance::where('employee_id', $employee->id)
            ->whereNotNull('clock_out_time')
            ->orderBy('id', 'desc')
            ->first();

        $overTimeHour = Attendance::where('employee_id', $employee->id)
            ->whereNotNull('overtime_hours')
            ->where('overtime_hours', '!=', 0)
            ->orderByDesc('id')
            ->first();
        $employee_schedule = EmployeeSchedule::where('employee_id', $employee->id)
            ->latest('id')
            ->first();
        if (!$employee_schedule) {
            $message = $lang == 'en'
                ? 'No schedule assigned to employee'
                : 'لا يوجد جدول مخصص للموظف';
            return respondError($message, 404);
        }
        $today = now()->toDateString();

        // Check if schedule is not yet started or already ended
        if ($today < $employee_schedule->start_date || $today > $employee_schedule->end_date) {
            $message = $lang == 'en'
                ? 'You cannot login. Your work schedule has not started or has already ended.'
                : 'لا يمكنك تسجيل الدخول، لم يبدأ جدول عملك بعد أو انتهى بالفعل.';
            return respondError($message, 400);
        }
        $shift = $this->timeTableService->getTimetableForDate($employee->id, $today);
        if (!($shift['status'] == false)) {
            $shiftType = $lang == 'en' ? $shift['data']['timetable']['name_en'] : $shift['data']['timetable']['name_ar'];
            $employee->shift_start = $shift['data']['on_duty_time'];
            $employee->shift_end = $shift['data']['off_duty_time'];
            $employee->shift_type = $shiftType;
            $employee->employee_schedule_id = $shift['data']['employee_schedule_id'];
            $employee->cross_day = $shift['data']['cross_day'];
        } else {
            $employee->shift_start = null;
            $employee->shift_end = null;
            $employee->shift_type = null;
            $employee->employee_schedule_id = null;
            $employee->cross_day = null;
        }

        if ($employee->shift_start && $employee->shift_end) {
            $shiftDate = now()->toDateString();
            $start = Carbon::parse("$shiftDate {$employee->shift_start}");
            $end = Carbon::parse("$shiftDate {$employee->shift_end}");

            if ($employee->cross_day && $end->lessThan($start)) {
                $end->addDay();
            }

            $workHours = $end->diffInMinutes($start) / 60; // in hours
            $leaveWork = $start->copy()->addMinutes($end->diffInMinutes($start));
        } else {
            $workHours = null;
            $leaveWork = null;
        }

        $allLastClockIn = Attendance::where('employee_id', $employee->id)
            ->whereNotNull('clock_in_time')
            ->get();

        $allLastClockout = Attendance::where('employee_id', $employee->id)
            ->whereNotNull('clock_out_time')
            ->get();

        $allOverTimeHour = Attendance::where('employee_id', $employee->id)
            ->whereNotNull('overtime_hours')
            ->where('overtime_hours', '!=', 0)
            ->get();

        $allDataForActivity = [];

        $allLastClockIn->each(function ($lastClockIn) use (&$allDataForActivity, $lang) {
            $allDataForActivity[] = [
                'type' => 'clock_in',
                'title' => $lang == 'en' ? 'clock in' : 'تم تسجيل الحضور',
                'date' => $lastClockIn->date,
                'time' => Carbon::parse($lastClockIn->clock_in_time)->format('h:i A'),
                'late' => (bool) $lastClockIn->not_on_time,
            ];
        });

        $clockOutEmployee = $allLastClockout->map(function ($lastClockOut) use (&$allDataForActivity, $lang) {
            $allDataForActivity[] = [
                'type' => 'clock_out',
                'title' => $lang == 'en' ? 'clock out' : 'تم تسجيل الانصراف',
                'date' => $lastClockOut->date,
                'time' => Carbon::parse($lastClockOut->clock_out_time)->format('h:i A'),
                'late' => (bool) $lastClockOut->not_on_time,
            ];
        });

        $overTimeEmployee = $allOverTimeHour->map(function ($lastOverTime) use (&$allDataForActivity, $lang) {
            $allDataForActivity[] = [
                'type' => 'overTime',
                'title' => $lang == 'en' ? 'overTime' : 'وقت اضافي',
                'date' => $lastOverTime->date,
                'time' => Carbon::parse($lastOverTime->overtime_hours)->format('h:i A'),
                'late' => (bool) $lastOverTime->not_on_time,
            ];
        });

        $data = [
            'shift_start' => Carbon::parse($employee->shift_start)->format('h:i A'),
            'work_hours' => $workHours,
            'leave_work' => optional($leaveWork)?->format('h:i A'),
            'status' => optional($lastClockIn)->date == now()->toDateString(),
            // 'clock_in' => [
            //     'date' => $lastClockIn?->date ?? null,
            //     'clock_in_time' => $lastClockIn?->clock_in_time ?? null,
            //     'late' => $lastClockIn?->not_on_time ? true : false ?? null,
            // ],
            // 'clock_out' => [
            //     'date' => $lastClockout?->date ?? null,
            //     'clock_out_time' => $lastClockout?->clock_out_time ?? null,
            // ],
            // 'overTime' => [
            //     'date' => $overTimeHour?->date ?? null,
            //     'time' => $overTimeHour?->overtime_hours ?? null,
            // ],
            'allActivity' => $allDataForActivity
        ];

        return ResponseWithSuccessData($lang, $data, 1);
    }
}
