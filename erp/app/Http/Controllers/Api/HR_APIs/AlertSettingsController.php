<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEvent;
use App\Models\Department;
use App\Models\Employee;
use App\Models\TypeAlert;
use App\Models\TypeMessageActive;
use Illuminate\Http\Request;

class AlertSettingsController extends Controller
{
    public function allTypeAlert(Request $request)
    {
        $alerts = TypeAlert::select('id', 'name')->get();
        $lang = $request->header('lang', 'en');
        return ResponseWithSuccessData($lang, $alerts, 1);
    }

    public function typeNotificationAllow(Request $request)
    {
        $lang = $request->header('lang', 'en');
        $typeNotifications = TypeMessageActive::where("active", true)->get();
        $results = $typeNotifications->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
            ];
        });
        return ResponseWithSuccessData($lang, $results, 1);
    }

    public function sendNotification(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $user = auth('employee')->user();
            $validated = $request->validate([
                'type_alerts_id'   => 'required|integer|exists:type_alerts,id',
                'department_id'    => 'required|integer|exists:departments,id',
                'type_notification_id'    => 'required|integer|exists:type_message_actives,id',
                'employee_ids'     => 'required|array',
                'employee_ids.*'   => 'integer|exists:employees,id',
            ]);

            $validated['employee_ids'] = json_encode($validated['employee_ids']);
            $validated['created_by'] = $user->id;
            $alert = TypeAlert::where('id', $validated['type_alerts_id'])->first();

            AttendanceEvent::create($validated);
            $not = [];
            $employees = [];
            $department = Department::where('id', $validated['department_id'])->first();
            foreach ($request->employee_ids as $employee) {
                $employee = Employee::where('id', $employee)->first();
                $employees[] = $employee->first_name . ' ' . $employee->last_name;
                $not[] = send_push_notification(
                    $employee->device_token,
                    "تحذير للموظف {$employee->first_name} {$employee->last_name} لانه {$alert->name}",
                    "Warning to employee {$employee->first_name} {$employee->last_name} because {$alert->name}",
                    "رسالة تحذير",
                    "Alert Setting",
                    "employee",
                    $employee->id,
                    $user->id,
                    $user->id,
                    'ar',
                    7
                );
            }
            $created_emp_alert= Employee::where('id', $validated['created_by'])->first();

            $data = [
                "employees" => $employees,
                "department" => $department->name,
                'created_by' => $created_emp_alert->first_name . ' ' . $created_emp_alert->last_name,

            ];

            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return RespondWithBadRequestData($lang, 2);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function showAttendanceEvents(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            // Retrieve all Attendance Events with optional relationships if needed
            $events = AttendanceEvent::with(['typeAlert', 'department', 'typeNotification'])->get();



            // dd($events);
            // Decode employee_ids if they are stored as JSON
            $data = $events->map(function ($event) {
                $employeeIds = json_decode($event->employee_ids, true);

                $employeeNames = Employee::whereIn('id', $employeeIds)
                    ->select('first_name', 'last_name')
                    ->get()
                    ->map(function ($employee) {
                        return $employee->first_name . ' ' . $employee->last_name;
                    });

                return [
                    'id' => $event->id,
                    'type_alert_name' => optional($event->typeAlert)->name,
                    'department_name' => optional($event->department)->name,
                    'type_notification_name' => optional($event->typeNotification)->name,
                    'employees' => $employeeNames,
                ];
            });

            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($request->header('lang', 'en'), 2);
        }
    }
}
