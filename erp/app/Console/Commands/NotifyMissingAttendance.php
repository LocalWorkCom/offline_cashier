<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\ShiftDetail;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Notifications\MissingAttendanceNotification;
use Carbon\Carbon;

class NotifyMissingAttendance extends Command
{
    protected $signature = 'attendance:notify-missing';
    protected $description = 'Notify employees who missed check-in or check-out';

    public function handle()
    {
        $todayIndex = Carbon::now()->dayOfWeek; // 0 = Sunday
        $todayDate = Carbon::today()->toDateString();

        $employees = Employee::with(['schedules.shiftDetails'])->get();

        foreach ($employees as $employee) {
            $latestSchedule = $employee->schedules()->latest()->first();
            if (!$latestSchedule) {
                continue;
            }

            $shiftDetail = ShiftDetail::where('shift_id', $latestSchedule->shift_id)
                ->where('day_index', $todayIndex)
                ->first();

            if (!$shiftDetail) {
                continue;
            }

            // ✅ Skip if employee is on leave today
            $onLeave = LeaveRequest::where('employee_id', $employee->id)
                ->where('status', 'confirm')
                ->whereDate('from', '<=', $todayDate)
                ->whereDate('to', '>=', $todayDate)
                ->exists();

            if ($onLeave) {
                continue;
            }

            // ✅ Check attendance
            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('created_at', $todayDate)
                ->first();

            if (!$attendance || !$attendance->clock_in_time) {
                // Missing check-in
                send_push_notification(
                    $employee->device_token,
                    "لم تقم يتسجيل الحضور اليوم",
                    "You missed your check-in today",
                    "تنبيه الحضور",
                    "Attendance Alert",
                    "employee",
                    $employee->id,
                    null,
                    null,
                    'ar',
                    5
                );
                // $this->warn("Employee {$employee->id} missed check-in today.");
            } elseif (!$attendance->clock_out_time && Carbon::now()->gt(Carbon::parse($shiftDetail->off_duty_time))) {
                // Missing check-out (after shift ends)
                // $employee->notify(new MissingAttendanceNotification('check-out'));
                send_push_notification(
                    $employee->device_token,
                    "لم تقم يتسجيل الانصراف اليوم",
                    "You missed your check-out today",
                    "تنبيه الحضور",
                    "Attendance Alert",
                    "employee",
                    $employee->id,
                    null,
                    null,
                    'ar',
                    5
                );


                // $this->warn("Employee {$employee->id} missed check-out today.");
            }
        }

        $this->info('Missing attendance notifications sent.');
    }
}
