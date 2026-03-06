<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\ShiftDetail;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class CheckEmployeeAttendance extends Command
{
    protected $signature = 'attendance:check-missing';
    protected $description = 'Check if employees did not check in today according to their shift schedule';

    public function handle()
    {
        $todayIndex = Carbon::now()->dayOfWeek; // 0 = Sunday
        $todayDate = Carbon::today()->toDateString();

        // Get all employees with schedules
        $employees = Employee::with(['schedules.shiftDetails'])
            ->get();

        foreach ($employees as $employee) {
            $latestSchedule = $employee->schedules()->latest()->first();

            if (!$latestSchedule) {
                continue;
            }

            // Get today’s shift detail
            $shiftDetail = ShiftDetail::where('shift_id', $latestSchedule->shift_id)
                ->where('day_index', $todayIndex)
                ->first();

            if (!$shiftDetail) {
                continue; // employee has no shift today
            }

            // Check if employee has attendance record today
            $hasAttendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('created_at', $todayDate)
                ->whereNotNull('clock_in_time')
                ->exists();
            // ✅ Check if employee has leave request today
            $hasLeave = LeaveRequest::where('employee_id', $employee->id)
                ->where('status', 'confirm') // only approved leaves
                ->whereDate('from', '<=', $todayDate)
                ->whereDate('to', '>=', $todayDate)
                ->exists();

            if ($hasLeave) {
                // Skip warning, employee is on leave
                continue;
            }


            if (!$hasAttendance) {
                // Employee did not check in
                $HrManagers = Employee::whereHas('user.roles', function ($q) {
                    $q->where('name', 'hr_manager');
                })->get();
                foreach ($HrManagers as $HrManager) {
                    send_push_notification(
                        $HrManager->device_token,
                        "لم يقم الموظف {$employee->first_name} {$employee->last_name} بتسجيل الحضور اليوم.",
                        "Employee {$employee->first_name} {$employee->last_name} did not check in today.",
                        "تنبيه الحضور",
                        "Attendance Alert",
                        "employee",
                        $HrManager->id,
                        null,
                        null,
                        'ar',
                        5
                    );
                }
                $this->warn("Employee {$employee->id} missed check-in today.");
            }
        }

        $this->info('Attendance check completed.');
        return 0;
    }
}
