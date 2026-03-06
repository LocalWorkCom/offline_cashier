<?php

namespace App\Services\HR_Services;

use App\Models\BiometricTransaction;
use App\Models\Attendance;
use App\Models\HrSetting;
use App\Models\Employee;
use App\Models\Timetable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\HR_Services\EmployeeAttendanceService;
use Illuminate\Support\Facades\DB;

class BioTimeService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.biotime.base_url');
    }

    /**
     * Process clock in/out punch
     */
    public function processPunch(Request $request, $punchState, $lang = 'ar')
    {
        try {
            // ✅ Validate request
            $validator = Validator::make($request->all(), [
                'lat' => ['required', 'numeric', 'between:-90,90'],
                'lng' => ['required', 'numeric', 'between:-180,180'],
            ]);

            if ($validator->fails()) {
                $message = $lang == 'en' ? 'Invalid location data' : 'بيانات الموقع غير صالحة';
                return respondError($message, 400, $validator->errors());
            }

            $employee = auth('employee')->user();
            $lat = $request->input('lat');
            $lng = $request->input('lng');

            if (!$employee) {
                $message = $lang == 'en' ? 'Employee not found' : 'الموظف غير موجود';
                return respondError($message, 404);
            }

            if (!$employee->branch_id) {
                $message = $lang == 'en' ? 'Employee must be assigned to a branch' : 'يجب تعيين الموظف إلى فرع';
                return respondError($message, 400);
            }

            $now = Carbon::now();

            $todayTimetableId = getEmployeeTodayTimetableId($employee->id);
            // if (!$todayTimetableId) {
            // $message = $lang == 'en' ? 'No timetable assigned for today' : 'لا يوجد جدول زمني معين لليوم';
            // return respondError($message, 400);
            // }

            $timetable = Timetable::find($todayTimetableId);
            // if (!$timetable) {
            //     $message = $lang == 'en' ? 'Timetable not found' : 'الجدول الزمني غير موجود';
            //     return respondError($message, 400);
            // }

            // 🔄 Start manual transaction
            DB::beginTransaction();

            // Check for duplicate punch
            $duplicateCheck = $this->checkDuplicatePunch($employee->employee_code, $punchState, $now, $employee->branch_id, $lang);
            if (!$duplicateCheck['allowed']) {
                DB::rollBack(); // rollback here
                $errorKey = $lang == 'en' ? 'duplicate_punch' : 'duplicate_punch';
                $errorData = [$errorKey => [$duplicateCheck['message']]];
                return respondError($duplicateCheck['message'], 400, $errorData);
            }

            // Check if trying to clock out without clocking in first
            if ($punchState == 1) { // Clock out
                $clockInCheck = $this->checkClockInBeforeClockOut($employee->id, $now->toDateString(), $lang);
                if (!$clockInCheck['allowed']) {
                    DB::rollBack();
                    return respondError($clockInCheck['message'], 400);
                }
            }

            // Create biometric transaction
            $biometricTransaction = $this->createBiometricTransaction($employee, $punchState, $now);
            if (!$biometricTransaction) {
                DB::rollBack();
                $message = $lang == 'en' ? 'Failed to create biometric transaction' : 'فشل في إنشاء معاملة القياسات الحيوية';
                return respondError($message, 400);
            }

            // Update or create attendance record
            $attendanceResult = $this->updateAttendanceRecord(
                $employee,
                $punchState,
                $now,
                $biometricTransaction,
                $lat,
                $lng
            );

            $EmployeeAttendanceService = app(EmployeeAttendanceService::class);

            $checkAttendance = $EmployeeAttendanceService->getAttendanceStatus($attendanceResult);

            if (strpos($checkAttendance, 'Late') !== false || strpos($checkAttendance, 'Early Departure') !== false) {
                $attendanceResult->not_on_time = 1;
            } elseif ($checkAttendance === 'Present') {
                $attendanceResult->not_on_time = 0;
            } elseif ($checkAttendance === 'Absent') {
                // $attendanceResult->not_on_time = 1; // 🚨 absent should be marked as not on time
            } else {
                $attendanceResult->not_on_time = 0;
            }


            $attendanceResult->save();

            // ✅ Commit only if everything succeeded
            DB::commit();

            // 🔔 send notification here if late or absent
            send_push_notification(
                $employee->device_token,
                "انت متأخر اليوم",
                "You are late today",
                "تنبيه تأخير",
                "Late Alert",
                "employee",
                $employee->id,
                null,
                null,
                'ar',
                5
            );
            return [
                'code' => 200,
                'status' => true,
                // 'success' => true,
                'message' => $punchState == 0
                    ? ($lang == 'en' ? 'Clock in successful' : 'تم تسجيل الدخول بنجاح')
                    : ($lang == 'en' ? 'Clock out successful' : 'تم تسجيل الخروج بنجاح'),
                'data' => [
                    'biometric_transaction' => $biometricTransaction,
                    'attendance' => $attendanceResult
                ]
            ];
        } catch (\Exception $e) {
            DB::rollBack(); // rollback in case of unexpected exception
            Log::error('Error processing punch: ' . $e->getMessage());
            $message = $lang == 'en'
                ? 'An error occurred while processing the punch'
                : 'حدث خطأ أثناء معالجة النقرة';
            return respondError($message, 400);
        }
    }



    /**
     * Check if punch is duplicate within threshold time
     */
    private function checkDuplicatePunch($empCode, $punchState, $currentTime, $branchId, $lang = 'ar')
    {
        $thresholdMinutes = HrSetting::getDuplicatePunchThreshold($branchId);
        $thresholdTime = $currentTime->copy()->subMinutes($thresholdMinutes);

        $recentPunch = BiometricTransaction::where('emp_code', $empCode)
            ->where('punch_state', $punchState)
            ->where('punch_time', '>=', $thresholdTime)
            ->latest('punch_time')
            ->first();

        if ($recentPunch) {
            $timeDiff = $currentTime->diffInMinutes($recentPunch->punch_time);
            $waitTime = $thresholdMinutes - $timeDiff;

            if ($lang == 'en') {
                $punchType = $punchState == 0 ? 'clock in' : 'clock out';
                $message = "Duplicate punch detected. Last {$punchType} was {$timeDiff} minutes ago. Please wait {$waitTime} more minutes.";
            } else {
                $punchType = $punchState == 0 ? 'دخول' : 'خروج';
                $message = "تم اكتشاف نقرة مكررة. آخر {$punchType} كان منذ {$timeDiff} دقيقة. يرجى الانتظار {$waitTime} دقيقة أخرى.";
            }

            return [
                'allowed' => false,
                'message' => $message
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Check if employee has clocked in before trying to clock out
     */
    private function checkClockInBeforeClockOut($employeeId, $date, $lang = 'ar')
    {
        $attendance = Attendance::where('employee_id', $employeeId)
            ->where('date', $date)
            ->first();

        // If no attendance record exists or no clock in time, prevent clock out
        if (!$attendance || !$attendance->clock_in_time) {
            if ($lang == 'en') {
                $message = "You cannot clock out as you didn't clock in today. Please report to HR to be able to clock out.";
            } else {
                $message = "لا يمكنك تسجيل الخروج حيث لم تسجل الدخول اليوم. يرجى مراجعة قسم الموارد البشرية لتتمكن من تسجيل الخروج.";
            }

            return [
                'allowed' => false,
                'message' => $message
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Create biometric transaction record
     */
    private function createBiometricTransaction($employee, $punchState, $punchTime)
    {
        $data = array_merge([
            'emp_code' => $employee->employee_code,
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'department' => $employee->department->name ?? null,
            'position' => $employee->position->name ?? null,
            'punch_time' => $punchTime,
            'punch_state' => $punchState,
            'punch_state_display' => $punchState == 0 ? 'Clock In' : 'Clock Out',
            'verify_type' => 1, // Default to fingerprint
            'verify_type_display' => 'Fingerprint',
            'upload_time' => $punchTime,
        ]);

        return BiometricTransaction::create($data);
    }

    /**
     * Update or create attendance record
     */
    private function updateAttendanceRecord($employee, $punchState, $punchTime, $biometricTransaction, $lat = null, $lng = null)
    {
        $date = $punchTime->toDateString();
        $time = $punchTime->toTimeString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $date)
            ->first();

        if (!$attendance) {
             $employeeSchedule = $employee->scheduleForDate($date);
            $attendanceData = [
                'employee_id' => $employee->id,
                'date' => $date,
                'biometric_transaction_id' => $biometricTransaction->id,
                'created_by' => Auth::id(),
                'latitude' => $lat,
                'longitude' => $lng,
            ];

            if ($punchState == 0) {
                $attendanceData['clock_in_time'] = $time;
                $attendanceData['latitude_in']   = $lat;
                $attendanceData['longitude_in']  = $lng;

                // Calculate late minutes for clock in
                $metrics = calculateAttendanceMetrics($employee, $date, $time, null);
                $attendanceData['late_minutes'] = $metrics['late_minutes'];

                // Store numeric values in database (convert from total_minutes)
                if (isset($metrics['total_minutes'])) {
                    $attendanceData['total_hours'] = round($metrics['total_minutes'] / 60, 2);
                }
                if (isset($metrics['overtime_minutes'])) {
                    $attendanceData['overtime_hours'] = round($metrics['overtime_minutes'] / 60, 2);
                }
            } else {
                $attendanceData['latitude_out']   = $lat;
                $attendanceData['longitude_out']  = $lng;
                $attendanceData['clock_out_time'] = $time;
            }
            $attendanceData['employee_schedule_id'] = $employeeSchedule ? $employeeSchedule->id : null;

            $attendance = Attendance::create($attendanceData);
        } else {
            $updateData = ['updated_by' => Auth::id()];

            if ($punchState == 0) {
                // Clock in - only update if no clock in time exists or this is earlier
                if (!$attendance->clock_in_time || $time < $attendance->clock_in_time) {
                    $updateData['clock_in_time'] = $time;
                    $updateData['biometric_transaction_id'] = $biometricTransaction->id;
                    $updateData['latitude_in']   = $lat;
                    $updateData['longitude_in']  = $lng;
                    // Calculate late minutes for clock in
                    $metrics = calculateAttendanceMetrics($employee, $date, $time, $attendance->clock_out_time);
                    $updateData['late_minutes'] = $metrics['late_minutes'];

                    // If there's already a clock out time, recalculate all metrics
                    if ($attendance->clock_out_time) {
                        // Store numeric values in database (convert from minutes)
                        if (isset($metrics['total_minutes'])) {
                            $updateData['total_hours'] = round($metrics['total_minutes'] / 60, 2);
                        }
                        if (isset($metrics['overtime_minutes'])) {
                            $updateData['overtime_hours'] = round($metrics['overtime_minutes'] / 60, 2);
                        }
                        $updateData['early_departure_minutes'] = $metrics['early_departure_minutes'];
                    }
                }
            } else {
                $updateData['clock_out_time'] = $time;
                $updateData['latitude_out']   = $lat;
                $updateData['longitude_out']  = $lng;
                // Calculate all metrics if both clock in and out times exist
                if ($attendance->clock_in_time) {
                    $metrics = calculateAttendanceMetrics($employee, $date, $attendance->clock_in_time, $time);

                    // Store numeric values in database (convert from minutes)
                    if (isset($metrics['total_minutes'])) {
                        $updateData['total_hours'] = round($metrics['total_minutes'] / 60, 2);
                    }
                    if (isset($metrics['overtime_minutes'])) {
                        $updateData['overtime_hours'] = round($metrics['overtime_minutes'] / 60, 2);
                    }
                    $updateData['early_departure_minutes'] = $metrics['early_departure_minutes'];

                    // Keep existing late_minutes from clock in, don't overwrite
                    if ($attendance->late_minutes === null) {
                        $updateData['late_minutes'] = $metrics['late_minutes'];
                    }
                }
            }

            $attendance->update($updateData);
        }

        return $attendance;
    }
}
