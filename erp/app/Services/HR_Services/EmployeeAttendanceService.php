<?php

namespace App\Services\HR_Services;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\Employee;
use App\Models\BiometricTransaction;
use App\Models\EmployeeSchedule;
use App\Models\LeaveRequest;
use App\Models\ShiftDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class EmployeeAttendanceService
{
    /**
     * Get my attendance (for authenticated user)
     */
    public function getMyAttendance($filters = [], $lang = 'ar')
    {
        $employee = auth('employee')->user();

        if (!$employee) {
            return [
                'error' => true,
                'message' => $lang == 'en' ? 'Employee not found' : 'الموظف غير موجود',
                'data' => []
            ];
        }

        return $this->getAttendanceByEmployeeId($employee->id, $filters, $lang);
    }

    /**
     * Get attendance by employee ID
     */
    public function getAttendanceByEmployeeId($employeeId, $filters = [], $lang = 'ar')
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return [
                'error' => true,
                'message' => $lang == 'en' ? 'Employee not found' : 'الموظف غير موجود',
                'data' => []
            ];
        }

        $query = Attendance::where('employee_id', $employeeId)
            ->with(['employee', 'biometricTransaction']);

        // Apply filters
        $query = $this->applyFilters($query, $filters);

        $attendances = $query->orderBy('date', 'desc')->get();

        return [
            'data' => [
                'attendances' => $attendances->map(function ($attendance) {
                    return $this->formatAttendanceData($attendance);
                })
            ]
        ];
    }

    /**
     * Get all employees attendance with filters
     */
    public function getAllAttendance($filters = [])
    {
        $employee = auth('employee')->user();
        $query = Attendance::with(['employee.department', 'employee.position', 'biometricTransaction']);

        // Apply filters
        $query = $this->applyFilters($query, $filters);

        if (!empty($filters['employee_ids'])) {
            $query->whereIn('employee_id', $filters['employee_ids']);
        }

        if (!empty($filters['department_ids'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->whereIn('department_id', $filters['department_ids']);
            });
        }

        if(!empty($filters['search'])){
            $search = $filters['search'];
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['branch_ids'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->whereIn('branch_id', $filters['branch_ids']);
            });
        }

        if (!empty($filters['position_ids'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->whereIn('position_id', $filters['position_ids']);
            });
        }

        $attendances = (clone $query)->orderBy('date', 'desc')->where('employee_id', $employee->id)
            ->orderBy('employee_id', 'asc')
            ->get();

        $child_employees = getSupervisedEmployees($employee->id);
        $childIds = $child_employees->pluck('id')->toArray();
        $attendances_employees = (clone $query)->orderBy('date', 'desc')->whereIn('employee_id', $childIds)
        ->orderBy('employee_id', 'asc')
        ->get();

        return [
            'data' => [
                'my' => $attendances->map(function ($attendance) {
                    return $this->formatAttendanceData($attendance, true);
                }),
                'employees' => $attendances_employees->map(function ($attendances_employee) {
                    return $this->formatAttendanceData($attendances_employee, true);
                }),
            ]
        ];
        // return [
        //     'data' => [
        //         'attendances' => $attendances->map(function ($attendance) {
        //             return $this->formatAttendanceData($attendance, true);
        //         }),
        //     ]
        // ];
    }

    /**
     * Get attendance summary/statistics
     */
    public function getAttendanceSummary($employeeId = null, $filters = [])
    {
        $query = Attendance::query()
            ->with(['employee', 'suspension']);

        if ($employeeId) {
            $query->where('attendances.employee_id', $employeeId);
        }


        $query = $this->applyFilters($query, $filters);
        $attendances = $query->get();

        // Calculate totals handling both numeric and formatted values
        $totalWorkingHours = 0;
        $totalOvertimeHours = 0;
        $overtimeDays = 0;

        foreach ($attendances as $attendance) {
            // Handle total hours
            if (is_numeric($attendance->total_hours)) {
                $totalWorkingHours += $attendance->total_hours;
            } else {
                $totalWorkingHours += $this->convertFormattedTimeToHours($attendance->total_hours);
            }

            // Handle overtime hours
            $overtimeValue = 0;
            if (is_numeric($attendance->overtime_hours)) {
                $overtimeValue = $attendance->overtime_hours;
            } else {
                $overtimeValue = $this->convertFormattedTimeToHours($attendance->overtime_hours);
            }

            $totalOvertimeHours += $overtimeValue;
            if ($overtimeValue > 0) {
                $overtimeDays++;
            }
        }

        // Calculate absent days based on employee schedule
        $absentDays = 0;
        if ($employeeId) {
            $absentDays = $this->calculateAbsentDays($employeeId, $filters, $attendances);
        }

        $summary = [
            'total_days' => $employeeId ? $this->calculateScheduledDays($employeeId, $filters) : $attendances->count(), //20
            'present_days' => $attendances->whereNotNull('clock_in_time')->count(), //2
            'absent_days' => $absentDays, //2
            'late_days' => $attendances->where('late_minutes', '>', 0)->count(), //1
            'overtime_days' => $overtimeDays, //1
            // 'suspension_duration' => $attendances->whereNotNull('suspension')->sum('suspension.suspension_duration'),
            // 'suspension_pending' => $attendances->whereNotNull('suspension')->where('suspension.approval_status', 'pending')->count('suspension.approval_status'),
            // 'suspension_approved' => $attendances->whereNotNull('suspension')->where('suspension.approval_status', 'approved')->count('suspension.approval_status'),
            // 'suspension_rejected' => $attendances->whereNotNull('suspension')->where('suspension.approval_status', 'rejected')->count('suspension.approval_status'),
            'early_departure_days' => $attendances->where('early_departure_minutes', '>', 0)->count(),
            'total_working_hours' => $this->convertDecimalHoursToFormatted($totalWorkingHours),
            'total_working_hours_numeric' => round($totalWorkingHours, 2),
            'total_overtime_hours' => $this->convertDecimalHoursToFormatted($totalOvertimeHours),
            'total_overtime_hours_numeric' => round($totalOvertimeHours, 2),
            'total_late_minutes' => $attendances->sum('late_minutes'),
            'total_early_departure_minutes' => $attendances->sum('early_departure_minutes'),
            'average_working_hours' => $attendances->count() > 0 ? $this->convertDecimalHoursToFormatted(round($totalWorkingHours / $attendances->whereNotNull('clock_in_time')->count(), 2)) : '0 hours 0 minutes',
            'average_working_hours_numeric' => $attendances->count() > 0 ? round($totalWorkingHours / $attendances->whereNotNull('clock_in_time')->count(), 2) : 0,
        ];

        return [
            'data' => $summary
        ];
    }

    /**
     * Get daily attendance report
     */
    public function getDailyAttendanceReport($date, $filters = [])
    {
        $date = Carbon::parse($date)->toDateString();

        $query = Attendance::whereDate('date', $date)
            ->with(['employee.department', 'employee.position', 'biometricTransaction']);

        // Apply branch filter
        if (!empty($filters['branch_ids'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->whereIn('branch_id', $filters['branch_ids']);
            });
        }

        // Apply department filter
        if (!empty($filters['department_ids'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->whereIn('department_id', $filters['department_ids']);
            });
        }

        // Apply position filter
        if (!empty($filters['position_ids'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->whereIn('position_id', $filters['position_ids']);
            });
        }

        $attendances = $query->orderBy('clock_in_time', 'asc')->get();
        $absentDays = 0;
        // Count overtime employees handling both formats
        $overtimeEmployees = 0;
        foreach ($attendances as $attendance) {
            $overtimeHours = $attendance->overtime_hours;
            if (is_numeric($overtimeHours)) {
                if ($overtimeHours > 0) {
                    $overtimeEmployees++;
                }
            } else {
                if ($overtimeHours !== '0 hours 0 minutes' && !empty($overtimeHours)) {
                    $overtimeEmployees++;
                }
            }
            $absentDays = $this->calculateAbsentDays($attendance->employee_id, ['start_date' => $date, 'end_date' => $date], $attendances);
        }

        $statistics = [
            'total_employees' => $attendances->count(),
            'present_employees' => $attendances->where('clock_in_time', '!=', null)->count(),
            'absent_employees' => $absentDays,
            'late_employees' => $attendances->where('late_minutes', '>', 0)->count(),
            'overtime_employees' => $overtimeEmployees,
            'early_departure_employees' => $attendances->where('early_departure_minutes', '>', 0)->count(),
        ];

        return [
            'data' => [
                'date' => $date,
                'statistics' => $statistics,
                'attendances' => $attendances->map(function ($attendance) {
                    return $this->formatAttendanceData($attendance, true);
                })
            ]
        ];
    }

    /**
     * Apply common filters to attendance query
     */
    private function applyFilters($query, $filters)
    {
        // Date range filters
        if (!empty($filters['start_date'])) {
            $query->where('date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('date', '<=', $filters['end_date']);
        }

        // Single date filter
        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        // Status filters
        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'present':
                    $query->whereNotNull('clock_in_time');
                    break;
                case 'absent':
                    $query->whereNull('clock_in_time');
                    break;
                case 'late':
                    $query->where('late_minutes', '>', 0);
                    break;
                case 'overtime':
                    // Handle both numeric and formatted overtime values
                    $query->where(function ($q) {
                        $q->where(function ($subQ) {
                            // For numeric values
                            $subQ->whereRaw('CAST(overtime_hours AS DECIMAL(8,2)) > 0');
                        })->orWhere(function ($subQ) {
                            // For formatted strings (not "0 hours 0 minutes")
                            $subQ->where('overtime_hours', '!=', '0 hours 0 minutes')
                                ->whereNotNull('overtime_hours')
                                ->where('overtime_hours', '!=', '');
                        });
                    });
                    break;
                case 'early_departure':
                    $query->where('early_departure_minutes', '>', 0);
                    break;
            }
        }

        // Month and year filters
        if (!empty($filters['month']) && !empty($filters['year'])) {
            $query->whereMonth('date', $filters['month'])
                ->whereYear('date', $filters['year']);
        } elseif (!empty($filters['year'])) {
            $query->whereYear('date', $filters['year']);
        } elseif (!empty($filters['month'])) {
            $query->whereMonth('date', $filters['month']);
        }

        return $query;
    }

    /**
     * Format attendance data for API response
     */
    private function formatAttendanceData($attendance, $includeEmployee = false)
    {
        $lang = app()->getLocale();
        $totalHours = $attendance->total_hours;
        $overtimeHours = $attendance->overtime_hours;

        // If stored as minutes, convert to formatted string
        if (is_numeric($totalHours)) {
            $totalMinutes = round($totalHours * 60);
            $hours = intval($totalMinutes / 60);
            $mins = $totalMinutes % 60;
            $totalHoursFormatted = $hours . ' h ' . $mins . ' m';
        } else {
            $totalHoursFormatted = $totalHours;
        }

        if (is_numeric($overtimeHours)) {
            $overtimeMinutes = round($overtimeHours * 60);
            $hours = intval($overtimeMinutes / 60);
            $mins = $overtimeMinutes % 60;
            $overtimeHoursFormatted = $hours . ' h ' . $mins . ' m';
        } else {
            $overtimeHoursFormatted = $overtimeHours;
        }

        $data = [
            'id' => $attendance->id,
            'date' => $attendance->date,
            'clock_in_time'  => $attendance->clock_in_time
                ? Carbon::parse($attendance->clock_in_time)->format('h:i A')
                : null,

            'clock_out_time' => $attendance->clock_out_time
                ? Carbon::parse($attendance->clock_out_time)->format('h:i A')
                : null,
            'total_hours' => $totalHoursFormatted,
            'total_hours_numeric' => is_numeric($totalHours) ? $totalHours : $this->convertFormattedTimeToHours($totalHours),
            'overtime_hours' => $overtimeHoursFormatted,
            'overtime_hours_numeric' => is_numeric($overtimeHours) ? $overtimeHours : $this->convertFormattedTimeToHours($overtimeHours),
            'late_minutes' => $attendance->late_minutes,
            'early_departure_minutes' => $attendance->early_departure_minutes,
            'status' => $this->getAttendanceStatus($attendance),
            'attentance' => !$attendance->clock_in_time ? ($lang == 'en' ? 'Absent' : 'غائب') : ($lang == 'en' ? 'Present' : 'حاضر'),
            'created_at' => $attendance->created_at,
            'updated_at' => $attendance->updated_at,
        ];

        if ($includeEmployee && $attendance->employee) {
            $data['employee'] = [
                'id' => $attendance->employee->id,
                'email' => $attendance->employee->email,
                'employee_code' => $attendance->employee->employee_code,
                'first_name' => $attendance->employee->first_name,
                'last_name' => $attendance->employee->last_name,
                'full_name' => $attendance->employee->first_name . ' ' . $attendance->employee->last_name,
                'department' => $attendance->employee->department->name ?? null,
                'position' => $attendance->employee->position->name ?? null,
            ];
        }

        return $data;
    }

    /**
     * Get attendance status based on punch times and calculations
     */
    public function getAttendanceStatus($attendance)
    {
        $lang = app()->getLocale();
        if (!$attendance->clock_in_time) {
            return $lang == 'en' ? 'Absent' : 'غائب';
        }

        $statuses = [];

        if ($attendance->late_minutes > 0) {
            $statuses[] = $lang == 'en' ? 'Late' : 'متأخر';
        }

        // Handle both numeric and formatted overtime hours
        $overtimeHours = $attendance->overtime_hours;
        $hasOvertime = false;
        if (is_numeric($overtimeHours)) {
            $hasOvertime = $overtimeHours > 0;
        } else {
            $hasOvertime = $overtimeHours !== '0 h 0 m' && !empty($overtimeHours);
        }

        if ($hasOvertime) {
            $statuses[] = $lang == 'en' ? 'Overtime' : 'عمل اضافي';
        }

        if ($attendance->early_departure_minutes > 0) {
            $statuses[] = $lang == 'en' ? 'Early Departure' : 'مغادرة مبكرة';
        }

        if (empty($statuses)) {
            return $lang == 'en' ? 'Present' : 'حاضر';
        }

        return implode(', ', $statuses);
    }

    /**
     * Convert formatted time string to decimal hours
     */
    private function convertFormattedTimeToHours($formattedTime)
    {
        if (empty($formattedTime) || $formattedTime === '0 h 0 m') {
            return 0;
        }

        // Extract hours and minutes from format like "8 hours 25 minutes"
        preg_match('/(\d+) h (\d+) m/', $formattedTime, $matches);

        if (count($matches) >= 3) {
            $hours = intval($matches[1]);
            $minutes = intval($matches[2]);
            return round(($hours + ($minutes / 60)), 2);
        }

        return 0;
    }

    /**
     * Convert decimal hours to formatted time string
     */
    private function convertDecimalHoursToFormatted($decimalHours)
    {
        if ($decimalHours <= 0) {
            return '0 h 0 m';
        }

        $totalMinutes = round($decimalHours * 60);
        $hours = intval($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return $hours . ' h ' . $minutes . ' m';
    }

    /**
     * Calculate the total number of scheduled work days for an employee within the date range
     */
    private function calculateScheduledDays($employeeId, $filters)
    {
        // Get the date range
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;

        // If no date range provided, return attendance count as fallback
        if (!$startDate && !$endDate && empty($filters['month']) && empty($filters['year'])) {
            return Attendance::where('employee_id', $employeeId)->count();
        }

        // Set default date range if not provided
        if (!$startDate || !$endDate) {
            if (!empty($filters['month']) && !empty($filters['year'])) {
                $startDate = Carbon::create($filters['year'], $filters['month'], 1)->startOfMonth()->toDateString();
                $endDate = Carbon::create($filters['year'], $filters['month'], 1)->endOfMonth()->toDateString();
            } elseif (!empty($filters['year'])) {
                $startDate = Carbon::create($filters['year'], 1, 1)->startOfYear()->toDateString();
                $endDate = Carbon::create($filters['year'], 1, 1)->endOfYear()->toDateString();
            } else {
                // Fallback to current month if no specific range
                $startDate = Carbon::now()->startOfMonth()->toDateString();
                $endDate = Carbon::now()->endOfMonth()->toDateString();
            }
        }

        $employee = Employee::find($employeeId);
        if (!$employee) {
            return 0;
        }
        $schedule = Attendance::where('employee_id', $employee->id)->whereDate('date', $startDate)->first();
        if (!$schedule || !$schedule->shift) {
            return 0;
        }



        // Get shift details (which days employee works)
        $shiftDetails = $schedule->shift->details()->get();
        $workDays = $shiftDetails->pluck('day_index')->toArray(); // [0, 1, 2, 3, 4] for Sun-Thu

        // Count scheduled work days in the date range
        $scheduledDays = 0;
        $currentDate = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);

        while ($currentDate->lte($endDateCarbon)) {
            // Check if this day is a scheduled work day
            if (in_array($currentDate->dayOfWeek, $workDays)) {
                // Check if employee was scheduled to work on this specific date
                $attendance = Attendance::where('employee_id', $employee->id)->where('date', $currentDate)->first();
                if ($attendance) {
                    $schedule = $attendance->employeeSchedule;
                } else {
                    $dateSchedule = $employee->scheduleForDate($currentDate);
                }

                if ($dateSchedule && $dateSchedule->shift) {
                    $scheduledDays++;
                }
            }
            $currentDate->addDay();
        }

        return $scheduledDays;
    }

    /**
     * Calculate absent days (scheduled days - days with attendance records)
     */
    public function calculateAbsentDays($employeeId, $filters, $attendances)
    {
        $scheduledDays = $this->calculateScheduledDays($employeeId, $filters);

        $LeaveRequestService = app(LeaveRequestService::class);
        $EmployeeLeavesMonth = $LeaveRequestService->EmployeeLeavesMonth(
            $employeeId,
            $filters['start_date'],
            $filters['end_date']
        );
        // $EmployeeLeavesMonth = collect([
        //     (object)['day_unpaid' => 1, 'day_paid' => 0],
        //     (object)['day_unpaid' => 2, 'day_paid' => 1],
        // ]);

        // Sum paid and unpaid leaves
        $unpaidLeaves = $EmployeeLeavesMonth->sum('day_unpaid');
        $deduction_days = $EmployeeLeavesMonth->sum('deduction_days');
        $paidLeaves   = $EmployeeLeavesMonth->sum('day_paid');

        // ✅ 1. Fetch approved suspensions within the range
        $suspensions = \App\Models\TemporarySuspension::where('employee_id', $employeeId)
            ->where('approval_status', \App\Models\TemporarySuspension::STATUS_APPROVED)
            ->where(function ($query) use ($filters) {
                $query->whereBetween('start_date', [$filters['start_date'], $filters['end_date']])
                    ->orWhereBetween('end_date', [$filters['start_date'], $filters['end_date']])
                    ->orWhere(function ($q) use ($filters) {
                        $q->where('start_date', '<=', $filters['start_date'])
                            ->where('end_date', '>=', $filters['end_date']);
                    });
            })
            ->get();

        // ✅ 2. Sum the number of days in suspension (intersecting with the filter period)
        $suspensionDays = $suspensions->sum(function ($suspension) use ($filters) {
            $filterStart = Carbon::parse($filters['start_date']);
            $filterEnd = Carbon::parse($filters['end_date']);

            // Get the latest start date and the earliest end date
            $start = $suspension->start_date->greaterThan($filterStart)
                ? $suspension->start_date
                : $filterStart;

            $end = $suspension->end_date->lessThan($filterEnd)
                ? $suspension->end_date
                : $filterEnd;

            // If the suspension doesn't overlap with the filter range, skip it
            if ($start->gt($end)) {
                return 0;
            }

            return $start->diffInDays($end) + 1;
        });

        // ✅ 3. Subtract paid leaves and suspension days
        $scheduledDays -= $paidLeaves;
        $scheduledDays -= $suspensionDays;

        // ✅ 4. Attendance
        $daysWithAttendance = $attendances->count();

        // ✅ 5. Calculate absences
        $absentDays = max(0, $scheduledDays - $daysWithAttendance);
        return $absentDays + $deduction_days;
    }



    public function generateRangeReport($fromDate, $toDate)
    {
        $from = Carbon::parse($fromDate)->startOfDay();
        $to   = Carbon::parse($toDate)->endOfDay();

        $period = CarbonPeriod::create($from, $to);

        $report = [];
        $employees = Employee::with(['employeeSchedules.shiftDetails'])->get();

        foreach ($employees as $employee) {

            $latestSchedule = $employee->employeeSchedules()->wherebetween('start_date', [$from, $to])
                ->orWhere(function ($q) use ($from) {
                    $q->where('start_date', '<=', $from)
                        ->where(function ($q2) use ($from) {
                            $q2->whereNull('end_date')
                                ->orWhere('end_date', '>=', $from);
                        });
                })
                ->orderBy('start_date', 'desc')
                ->first();

            foreach ($period as $date) {
                $dayIndex = $date->dayOfWeek; // 0 = Sunday, 1 = Monday ...

                // If no schedule
                if (!$latestSchedule) {
                    $report[] = [
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->full_name,
                        'employee_department' => $employee->department->name ?? null,
                        'date'        => $date->toDateString(),
                        'status'      => 'no_schedule',
                        'message'     => 'Employee has no active schedule',
                    ];
                    continue;
                }
                // Get today’s shift detail
                $shiftDetail = ShiftDetail::where('shift_id', $latestSchedule->shift_id)
                    ->where('day_index', $dayIndex)
                    ->first();

                if (!$shiftDetail) {
                    $report[] = [
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->full_name,
                        'employee_department' => $employee->department->name ?? null,
                        'date'        => $date->toDateString(),
                        'status'      => 'no_shift',
                        'message'     => 'No shift assigned today',
                    ];
                    continue;
                }

                // Get attendance
                $attendance = Attendance::where('employee_id', $employee->id)
                    ->whereDate('date', $date->toDateString())
                    ->first();

                if (!$attendance) {
                    $report[] = [
                        'employee_id' => $employee->id,
                        'date'        => $date->toDateString(),
                        'employee_name' => $employee->full_name,
                        'employee_department' => $employee->department->name ?? null,
                        'status'      => 'absent',
                        'message'     => 'Shift exists but no attendance record',
                    ];
                    continue;
                }



                $report[] = [
                    'employee_id'       => $employee->id,
                    'employee_name' => $employee->full_name,
                    'employee_department' => $employee->department->name ?? null,
                    'date'              => $date->toDateString(),
                    'status'            =>  $this->getAttendanceStatus($attendance),
                    'check_in'          => $attendance->check_in,
                    'check_out'         => $attendance->check_out,
                    'late_minutes'      => $attendance->late_minutes,
                    'early_leave'       => $attendance->early_departure_minutes,
                    'working_hours'     => $attendance->total_hours,
                ];
            }
        }

        return $report;
    }

    public function lastActivity($lang = 'ar')
    {
        $employee = auth('employee')->user();
        $employee = Employee::with('shiftDetails')->find($employee->id);

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
        // if (!$employee_schedule) {
        //     $message = $lang == 'en'
        //         ? 'No schedule assigned to employee'
        //         : 'لا يوجد جدول مخصص للموظف';
        //     return respondError($message, 404);
        // }
        $today = now()->toDateString();

        // Check if schedule is not yet started or already ended
        if ($today < $employee_schedule->start_date || $today > $employee_schedule->end_date) {
            $message = $lang == 'en'
                ? 'You cannot login. Your work schedule has not started or has already ended.'
                : 'لا يمكنك تسجيل الدخول، لم يبدأ جدول عملك بعد أو انتهى بالفعل.';
            return respondError($message, 400);
        }
        $shift = $this->timeTableService->getTimetableForDate($user->id, $today);
        if (!($shift['status'] == false)) {
            $shiftType = $lang == 'en' ? $shift['data']['timetable']['name_en'] : $shift['data']['timetable']['name_ar'];
            $user->shift_start = $shift['data']['on_duty_time'];
            $user->shift_end = $shift['data']['off_duty_time'];
            $user->shift_type = $shiftType;
            $user->employee_schedule_id = $shift['data']['employee_schedule_id'];
            $user->cross_day = $shift['data']['cross_day'];
        } else {
            $user->shift_start = null;
            $user->shift_end = null;
            $user->shift_type = null;
            $user->employee_schedule_id = null;
            $user->cross_day = null;
        }
        dd($employee->shiftDetails);
        $data = [
            'clock_in' => [
                'date' => $lastClockIn?->date ?? null,
                'clock_in_time' => $lastClockIn?->clock_in_time ?? null,
                'late' => $lastClockIn?->not_on_time ? true : false ?? null,
            ],
            'clock_out' => [
                'date' => $lastClockout?->date ?? null,
                'clock_out_time' => $lastClockout?->clock_out_time ?? null,
            ],
            'overTime' => [
                'date' => $overTimeHour?->date ?? null,
                'time' => $overTimeHour?->overtime_hours ?? null,
            ]
        ];
        return $data;
    }
}
