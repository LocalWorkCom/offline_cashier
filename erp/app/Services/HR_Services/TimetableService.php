<?php

namespace App\Services\HR_Services;

use App\Models\EmployeeSchedule;
use App\Models\ShiftDetail;
use App\Models\Timetable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TimetableService
{
    public function index()
    {
        $timetables = Timetable::query();
        return $timetables;
    }
    public function show($id)
    {
        $timetable = Timetable::find($id);

        return $timetable;
    }

    public function store($request)
    {
        $user = auth('employee')->user();

        $data = $request->only([
            'name_ar',
            'name_en',
            'on_duty_time',
            'off_duty_time',
            'start_sign_in',
            'end_sign_in',
            'start_sign_out',
            'end_sign_out',
            'lateness_grace_period',
            'start_late_time_option',
            'cross_day'
        ]);

        $data['created_by'] = $user->id ?? auth('admin')->user()->id;

        $timetable = Timetable::create($data);

        return $timetable;
    }

    public function update($request, $id, $lang = 'ar')
    {
        $user = auth('employee')->user();
        try {
            $data = $request->only([
                'name_ar',
                'name_en',
                'on_duty_time',
                'off_duty_time',
                'start_sign_in',
                'end_sign_in',
                'start_sign_out',
                'end_sign_out',
                'lateness_grace_period',
                'start_late_time_option',
                'cross_day'
            ]);

            $data['modified_by'] = $user->id ?? auth('admin')->user()->id;

            $timetable = Timetable::find($id);
            $message = $lang == 'en' ? 'Timetable not found' : 'الجدول الزمني غير موجود';
            if (!$timetable)
                return ['status' => 'error', 'message' => $message];

            $timetable->update($data);

            return $timetable;
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => __('Failed to update timetable')
            ];
        }
    }


    public function delete($id, $lang = 'ar')
    {
        $timetable = Timetable::find($id);
        if (!$timetable) {
            $message = $lang == 'en' ? 'Timetable not found' : 'الجدول الزمني غير موجود';
            return ['status' => 'error', 'message' => $message, 'code' => 404];
        }
        $shiftDetailsCount = ShiftDetail::where('timetable_id', $id)->count();
        if ($shiftDetailsCount) {
            $message = $lang == 'en'
                ? 'The time table cannot be deleted because it is linked to shift.'
                : 'لا يمكن حذف الجدول الزمني لأنه مرتبط بالورديات.';
            return ['status' => 'error', 'message' => $message, 'code' => 400];
        }
        $timetable->update(['deleted_by' => auth()->id()]);

        $timetable->delete();

        $message = $lang == 'en' ? 'Timetable deleted successfully' : 'تم حذف الجدول الزمني بنجاح';
        return ['status' => 'success', 'message' => $message];
    }

    public static function getTimetableForDate($employeeId, $date)
    {
        try {
            $date = Carbon::parse($date);
            $dayIndex = $date->dayOfWeek;

            $schedule = EmployeeSchedule::where('employee_id', $employeeId)
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)->whereNull('deleted_at')
                ->first();
            if (!$schedule) {
                return ['status' => false, 'message' => 'No schedule found for the given date.'];
            }

            $shiftDetail = ShiftDetail::where('shift_id', $schedule->shift_id)
                ->where('day_index', $dayIndex)
                ->with('timetable')
                ->first();

            if (!$shiftDetail || !$shiftDetail->timetable) {
                return ['status' => false, 'message' => 'No timetable found for the given day.'];
            }

            return [
                'status' => true,
                'data' => [
                    'timetable' => $shiftDetail->timetable,
                    'employee_schedule_id' => $schedule->id,
                    'cross_day' => $shiftDetail->timetable->cross_day,
                    'on_duty_time' => $shiftDetail->timetable->on_duty_time,
                    'off_duty_time' => $shiftDetail->timetable->off_duty_time,
                    'start_sign_in' => $shiftDetail->timetable->start_sign_in,
                    'end_sign_in' => $shiftDetail->timetable->end_sign_in,
                    'start_sign_out' => $shiftDetail->timetable->start_sign_out,
                    'end_sign_out' => $shiftDetail->timetable->end_sign_out,
                    'lateness_grace_period' => $shiftDetail->timetable->lateness_grace_period,
                    'start_late_time_option' => $shiftDetail->timetable->start_late_time_option,
                ],
            ];
        } catch (\Exception $e) {
            Log::error("Error retrieving timetable for employee $employeeId on date $date: " . $e->getMessage());
            return ['status' => false, 'message' => 'Error retrieving timetable data.'];
        }
    }
}
