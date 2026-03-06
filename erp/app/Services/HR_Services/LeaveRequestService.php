<?php

namespace App\Services\HR_Services;

use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLeaveLog;
use App\Models\HRRequest;
use App\Models\LeaveType;
use App\Models\LeaveSetting;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestAgreement;
use App\Models\LeaveSettingPosition;
use App\Models\LeaveRequestPosition;
use App\Models\LeaveNational;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Services\HR_Services\HRServicesService;
use App\Services\HR_Services\HRRequestService;
use App\Services\HR_Services\HolidaysService;
use Google\Service\Datastream\Merge;

class LeaveRequestService
{
    private $lang;
    protected $hrServiceService;
    protected $hrRequestService;
    protected $holidaysService;

    public function __construct(HRServicesService $hrServiceService, HRRequestService $hrRequestService, HolidaysService $holidaysService, Request $request)
    {
        $this->lang = $request->header('lang', 'en');
        app()->setLocale($this->lang);
        $this->hrServiceService = $hrServiceService;
        $this->hrRequestService = $hrRequestService;
        $this->holidaysService = $holidaysService;
    }
    public function storeEmployeeLeaves($employee_id, $position_id, $leave_type_id, $day_count, $day_paid, $day_unpaid, $request_id)
    {
        try {
            $employeeLeave = EmployeeLeave::where('employee_id', $employee_id)
                ->where('position_id', $position_id)
                ->where('leave_type_id', $leave_type_id)
                ->first();

            if ($employeeLeave) {
                // Update existing record
                $employeeLeave->day_count += $day_count;
                $employeeLeave->day_paid += $day_paid;
                $employeeLeave->day_unpaid += $day_unpaid;
                $employeeLeave->save();
            } else {
                // Create new record
                EmployeeLeave::create([
                    'employee_id' => $employee_id,
                    'position_id' => $position_id,
                    'leave_type_id' => $leave_type_id,
                    'day_count' => $day_count,
                    'day_paid' => $day_paid,
                    'day_unpaid' => $day_unpaid,
                ]);
            }

            //add EmployeeLeaveLog
            $deduction_value = LeaveSetting::where('leave_type_id', $leave_type_id)->first()->deduction_value;
            $leave_request = LeaveRequest::find($request_id);
            $employeeLeaveLog = new EmployeeLeaveLog();
            $employeeLeaveLog->employee_id =  $employee_id;
            $employeeLeaveLog->position_id =  $position_id;
            $employeeLeaveLog->leave_type_id =  $leave_type_id;
            $employeeLeaveLog->leave_request_id =  $request_id;
            $employeeLeaveLog->date =  $leave_request->date;
            $employeeLeaveLog->from =  $leave_request->from;
            $employeeLeaveLog->to =  $leave_request->to;
            $employeeLeaveLog->day_count =  $day_count;
            $employeeLeaveLog->day_paid =  $day_paid;
            $employeeLeaveLog->day_unpaid =  $day_unpaid;
            $employeeLeaveLog->deduction_value =  $deduction_value;
            $employeeLeaveLog->deduction_days =  $day_unpaid * $deduction_value;
            $employeeLeaveLog->resone =  $leave_request->resone;
            $employeeLeaveLog->save();

            $leave_request->status = "confirm";
            $leave_request->save();

            // return true;
            return ResponseWithSuccessData($this->lang, null, 1);
        } catch (\Exception $e) {
            Log::error('Error in storeEmployeeLeaves: ' . $e->getMessage());
            return false;
        }
    }
    public function checkRemainLeaveRequests($request_id, $position_id)
    {

        try {
            $employeeLeave = LeaveRequest::find($request_id);
            $employee_id    =   $employeeLeave->employee_id;
            $position_id    =   $employeeLeave->position_id;
            $leave_type_id  =   $employeeLeave->leave_type_id;

            $LeaveSetting = LeaveSetting::where('leave_type_id', $leave_type_id)->first();
            $new_day_count      = $employeeLeave->leave_count;
            // $day_paid       = $LeaveSetting->day_paid;
            // $day_unpaid     = $LeaveSetting->day_unpaid;
            $day_paid       = 0;
            $day_unpaid     = 0;
            $used_day_count = 0;
            $used_day_paid  = 0;
            $used_day_unpaid  = 0;

            // $LeaveSettingPosition = LeaveSettingPosition::where('leave_setting_id', $LeaveSetting->id)->first();
            $LeaveSettingPosition = LeaveSettingPosition::where('leave_setting_id', $LeaveSetting->id)->whereJsonContains('higher_position_approve', $position_id)->first();
            if ($LeaveSetting) {
                $max_day_count = $LeaveSetting->day_count;
                $max_day_paid = $LeaveSetting->day_paid;
                $max_day_unpaid = $LeaveSetting->day_unpaid;

                $employee_leave = EmployeeLeave::where('employee_id', $employee_id)
                    ->where('position_id', $position_id)
                    ->where('leave_type_id', $leave_type_id)
                    ->first();

                if ($employee_leave) {
                    $used_day_count = $employee_leave->day_count;
                    $used_day_paid = $employee_leave->day_paid;
                    $used_day_unpaid = $employee_leave->day_unpaid;
                }
                // return $new_day_count;

                $remain_day_count = $max_day_count - ($used_day_count + $new_day_count);
                $remain_day_paid = $max_day_paid - ($used_day_paid);
                $remain_day_unpaid = $max_day_unpaid - ($used_day_unpaid);
                // $remain_day_paid = $max_day_paid - ($used_day_paid - $day_paid);
                // $remain_day_unpaid = $max_day_unpaid - ($used_day_unpaid - $day_unpaid);

                return [
                    'remain_day_count' => $remain_day_count,
                    'remain_day_paid' => $remain_day_paid,
                    'remain_day_unpaid' => $remain_day_unpaid,
                ];
            } else {
                return respondError($this->lang == 'en', 400, $this->lang == 'en' ? ['No setting for This postition'] : ['لا يوجد إعداد لهذا المنصب']);
            }
        } catch (\Exception $e) {
            Log::error('Error in checkRemainLeaveRequests: ' . $e->getMessage());
            return null;
        }
    }
    public function ChangeStatus($request)
    {
        $lang =  $request->header('lang', 'en');
        // try {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:leave_requests,id',
            'status' => 'required|string|in:pending,processing,confirm,reject',
            'day_paid' => 'nullable|integer',
            'day_unpaid' => 'nullable|integer',
            // 'day_paid' => 'required|integer',
            // 'day_unpaid' => 'required|integer',
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $hr_day_paid = $request->day_paid;
        $hr_day_unpaid = $request->day_unpaid;
        $employee = Auth::guard('employee')->user();
        $employee_posision_id = $employee->position_id;
        $leave_request = LeaveRequest::find($request->id);
        if (!$leave_request) {
            return respondError(
                $this->lang == 'en' ? 'Not existing any more' : 'غير موجود',
                400,
                $this->lang == 'en' ? ['error' => ['This item is not existing any more']] : ['error' => ['هذا العنصر غير موجود']]
            );
        }

        $hr_leave_count = $leave_request->leave_count;

        // Fetch leave settings
        $LeaveSetting = LeaveSetting::where('leave_type_id', $leave_request->leave_type_id)->where('country_id', $leave_request->employees?->country_id)->first();
        if (!$LeaveSetting) {
            return respondError(
                $this->lang == 'en' ? 'Not existing any more' : 'غير موجود',
                400,
                $this->lang == 'en' ? ['error' => ['This item is not existing any more']] : ['error' => ['اعدادات الاجازات لهذا العنصر غير موجود']]
            );
        }

        $LeaveSettingPosition = LeaveSettingPosition::where('leave_setting_id', $LeaveSetting->id)
            ->whereJsonContains('higher_position_approve', $employee_posision_id)
            ->first();

        if (!$LeaveSettingPosition) {
            return respondError(
                $this->lang == 'en' ? 'No setting for This position' : 'لا يوجد إعداد لهذا المنصب',
                400,
                $this->lang == 'en' ? ['error' => ['No setting for This position']] : ['error' => ['لا يوجد إعداد لهذا المنصب']]
            );
        }

        $higher_position_setting = $LeaveSettingPosition->higher_position_setting;
        $higher_position_approve = $LeaveSettingPosition->higher_position_approve;

        // ✅ If higher position approvals are required
        if ($higher_position_setting && $higher_position_approve) {
            $higher_position_approve_ids = json_decode($higher_position_approve, true);

            if (is_array($higher_position_approve_ids) && in_array($employee_posision_id, $higher_position_approve_ids)) {
                $check_LeaveRequestAgreement = LeaveRequestAgreement::where(['leave_request_id' => $leave_request->id, 'agreement_by' => $employee->id])->first();
                if ($check_LeaveRequestAgreement) {
                    return respondError(
                        $this->lang == 'en' ? 'It was dealt with before' : 'تم التعامل من قبل',
                        400,
                        $this->lang == 'en' ? ['error' => ['This request has been processed by']] : ['error' => ['تم التعامل مع هذا الطلب من قبل']]
                    );
                }


                if ($request->status == 'confirm') {
                    $Remaining_days = $this->checkRemainLeaveRequests($leave_request->id, $employee_posision_id);
                    // dd($Remaining_days);
                    if (!$Remaining_days) {
                        return respondError(
                            $this->lang == 'en' ? 'No setting for This position' : 'لا يوجد إعداد لهذا المنصب',
                            400,
                            $this->lang == 'en' ? ['error' => ['No setting for This position']] : ['error' => ['لا يوجد إعداد لهذا المنصب']]
                        );
                    }

                    //split
                    $leave_pattern = $LeaveSettingPosition->leave_pattern;
                    $split_max = $LeaveSettingPosition->split_max;
                    $setting_day_count = $LeaveSetting->day_count;
                    $setting_day_paid = $LeaveSetting->day_paid;
                    $setting_day_unpaid = $LeaveSetting->day_unpaid;

                    $employee_day_paid = 0;
                    $employee_day_unpaid = 0;
                    $employee_leave_count = 0;
                    $get_all_leave_request_log = EmployeeLeaveLog::where(['employee_id' => $leave_request->employee_id, 'leave_type_id' => $leave_request->leave_type_id])->get();
                    if ($get_all_leave_request_log) {
                        $employee_day_paid = $get_all_leave_request_log->sum('day_paid');
                        $employee_day_unpaid = $get_all_leave_request_log->sum('day_unpaid');
                        $employee_leave_count = $get_all_leave_request_log->sum('day_count');
                    }

                    if ($leave_pattern == "split" && $hr_day_paid > $split_max) {
                        return respondError(
                            $this->lang == 'en' ? 'This number of holidays is not allowed.' : 'غير مسموح بهذا العدد من الاجازات',
                            400,
                            $this->lang == 'en' ? ['error' => ['This number of holidays is not allowed. Only the permitted number is allowed: ' . $split_max]] : ['error' => ['غير مسموح بهذا العدد من الاجازات المسموح به فقط : ' . $split_max]]
                        );
                    }

                    // Validate remaining leave days
                    if ($Remaining_days['remain_day_count'] < 0 && $hr_day_unpaid == 0) {
                        return respondError(
                            $this->lang == 'en' ? 'Exceed the allowed number of days' : 'تجاوز العدد المسموح به من الأيام',
                            400,
                            $this->lang == 'en' ? ['error' => ['You have exceeded the allowed number of days for this leave type']] : ['error' => ['لقد تجاوزت العدد المسموح به من الأيام لهذا النوع من الإجازات']]
                        );
                    }

                    if ($Remaining_days['remain_day_paid'] < 0 && $hr_day_unpaid == 0) {
                        return respondError(
                            $this->lang == 'en' ? 'Exceed the allowed number of paid days' : 'تجاوز العدد المسموح به من الأيام المدفوعة',
                            400,
                            $this->lang == 'en' ? ['error' => ['You have exceeded the allowed number of paid days for this leave type']] : ['error' => ['لقد تجاوزت العدد المسموح به من الأيام المدفوعة لهذا النوع من الإجازات']]
                        );
                    }

                    // if ($Remaining_days['remain_day_unpaid'] < 0) {
                    //     return respondError(
                    //         $this->lang == 'en' ? 'Exceed the allowed number of unpaid days' : 'تجاوز العدد المسموح به من الأيام غير المدفوعة',
                    //         400,
                    //         $this->lang == 'en' ? ['You have exceeded the allowed number of unpaid days for this leave type'] : ['لقد تجاوزت العدد المسموح به من الأيام غير المدفوعة لهذا النوع من الإجازات']
                    //     );
                    // }
                }



                LeaveRequestAgreement::create([
                    'leave_request_id' => $leave_request->id,
                    'agreement_by' => $employee->id,
                    'agreement' => $request->status == 'confirm' ? 1 : 0,
                    'created_by' => authActionSave()['by'],
                    // 'created_by_type' => authActionSave()['type'],
                    'resone' => $request->reason ?? null,
                    'position_id' => $employee_posision_id ?? null,
                ]);
                if ($higher_position_setting == 'one') {

                    $leave_request->status = $request->status;
                    $leave_request->save();
                } else {
                    if ($request->status == 'confirm') {

                        $LeaveRequestAgreement = LeaveRequestAgreement::where('leave_request_id', $leave_request->id)
                            ->where('agreement', 1)
                            ->whereIn('position_id', $higher_position_approve_ids)
                            ->get();
                        if (count($LeaveRequestAgreement) == count($higher_position_approve_ids)) {
                            $leave_request->status = 'confirm';
                        } else {
                            $leave_request->status = 'processing';
                        }
                    } else {
                        $leave_request->status = 'reject';
                    }
                    $leave_request->save();
                }
                // Save agreement record


                if ($request->status == 'confirm') {

                    // $Remaining_days = $this->checkRemainLeaveRequests($leave_request->id, $employee_posision_id);
                    // // dd($Remaining_days);
                    // if (!$Remaining_days) {
                    //     return respondError(
                    //         $this->lang == 'en' ? 'No setting for This position' : 'لا يوجد إعداد لهذا المنصب',
                    //         400,
                    //         $this->lang == 'en' ? ['No setting for This position'] : ['لا يوجد إعداد لهذا المنصب']
                    //     );
                    // }

                    // // Validate remaining leave days
                    // if ($Remaining_days['remain_day_count'] <= 0) {
                    //     return respondError(
                    //         $this->lang == 'en' ? 'Exceed the allowed number of days' : 'تجاوز العدد المسموح به من الأيام',
                    //         400,
                    //         $this->lang == 'en' ? ['You have exceeded the allowed number of days for this leave type'] : ['لقد تجاوزت العدد المسموح به من الأيام لهذا النوع من الإجازات']
                    //     );
                    // }
                    // if ($Remaining_days['remain_day_paid'] <= 0) {
                    //     return respondError(
                    //         $this->lang == 'en' ? 'Exceed the allowed number of paid days' : 'تجاوز العدد المسموح به من الأيام المدفوعة',
                    //         400,
                    //         $this->lang == 'en' ? ['You have exceeded the allowed number of paid days for this leave type'] : ['لقد تجاوزت العدد المسموح به من الأيام المدفوعة لهذا النوع من الإجازات']
                    //     );
                    // }
                    // if ($Remaining_days['remain_day_unpaid'] <= 0) {
                    //     return respondError(
                    //         $this->lang == 'en' ? 'Exceed the allowed number of unpaid days' : 'تجاوز العدد المسموح به من الأيام غير المدفوعة',
                    //         400,
                    //         $this->lang == 'en' ? ['You have exceeded the allowed number of unpaid days for this leave type'] : ['لقد تجاوزت العدد المسموح به من الأيام غير المدفوعة لهذا النوع من الإجازات']
                    //     );
                    // }

                    // Store into employee_leaves
                    $storeResult = $this->storeEmployeeLeaves(
                        $leave_request->employee_id,
                        $leave_request->employees?->position_id,
                        $leave_request->leave_type_id,
                        $hr_leave_count,
                        $hr_day_paid,
                        $hr_day_unpaid,
                        $request->id
                    );

                    // Send notification for approved request
                    $this->sendLeaveRequestNotification($leave_request, 'approved', $this->lang);

                    return $storeResult;
                } else {
                    // Send notification for rejected request
                    $this->sendLeaveRequestNotification($leave_request, 'rejected', $this->lang);
                }
            } else {
                return respondError(
                    $this->lang == 'en' ? 'You are not authorized to approve/reject this request' : 'أنت غير مؤهل للموافقة / الرفض على هذا الطلب',
                    400,
                    $this->lang == 'en' ? ['error' => ['You are not authorized to approve/reject this request']] : ['error' => ['أنت غير مؤهل للموافقة / الرفض على هذا الطلب']]
                );
            }
        }

        return ResponseWithSuccessData($this->lang, $leave_request, 1);
    }

    // public function EmployeeLeaves($request)
    // {
    //     // $lang = $request->lang;
    //     $lang = $request->header('lang', 'en');

    //     $validator = Validator::make($request->all(), [
    //         'employee_id' => 'nullable|exists:employees,id'
    //     ]);

    //     if ($validator->fails()) {
    //         return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
    //     }

    //     $employee = Auth::guard('employee')->user();
    //     if ($employee->hasPermissionTo('create leave_requests_for_all_employees', 'employee')) {
    //         if ($request->employee_id) {
    //             $employee_id = $request->employee_id;
    //         } else {
    //             $employee_id = $employee->id;
    //         }
    //     } else {
    //         $employee_id = $employee->id;
    //     }
    //     if ($employee_id == null) {

    //         return respondError(
    //             $this->lang == 'en' ? 'Sorry, We did not found employee' : 'عفوا لا يوجد موظف ',
    //             400,
    //             $this->lang == 'en' ? ['Sorry, We did not found employee to make search for it'] : ['عفوا لا يوجد موظف ليتم البحث عليه']
    //         );
    //     }
    //     $employee = Employee::find($employee_id);
    //     $leaves_by_positions = LeaveSettingPosition::where('position_id', $employee->position_id)->with('leaveSettings.leaveTypes')->get();

    //     if ($leaves_by_positions->isEmpty()) {
    //         return respondError(($lang == 'en' ? 'No holidays' : 'لا يوجد اجازات'), 400, $lang == 'en' ? 'You do not have any holidays' : 'لا يوجد اجازات');
    //     }

    //     $employee_leaves = $leaves_by_positions->map(function ($leave) use ($employee) {
    //         $employee_leaves = EmployeeLeave::where(['employee_id' => $employee->id, 'position_id' => $employee->position_id, 'leave_type_id' => $leave->leaveSettings?->leave_type_id])->first();
    //         // if ($employee_leaves) {
    //             $total_day_count = $leave->leaveSettings?->day_count;
    //             $total_day_paid = $leave->leaveSettings?->day_paid;
    //             $total_day_unpaid = $leave->leaveSettings?->day_unpaid;
    //             $leave_name = $leave->leaveSettings?->leaveTypes?->name;
    //             $leave_id = $leave->leaveSettings?->leaveTypes?->id;
    //             $leave_image = $leave->leaveSettings?->leaveTypes?->image;
    //             return [
    //                 'leave_id' => $leave_id,
    //                 'leave_image' => $leave_image,
    //                 'leave_name' => $leave_name,
    //                 'leave_count' => $total_day_count,
    //                 'leave_paid' => $total_day_paid,
    //                 'leave_unpaid' => $total_day_unpaid,
    //                 'my_leave_count' => $employee_leaves['day_count'] ?? 0,
    //                 'my_leave_paid' => $employee_leaves['day_paid'] ?? 0,
    //                 'my_leave_unpaid' => $employee_leaves['day_unpaid'] ?? 0,
    //             ];
    //         // }
    //     });


    //     $countryCode = $employee->country->code;
    //     $date = Carbon::now();
    //     $leave_nationals = $this->holidaysService->getHolidays($countryCode, $date, $lang, "partial");
    //     $all_leave_nationals = $this->holidaysService->getHolidays($countryCode, $date, $lang, "all");
    //     $count_all_leave_nationals = count($all_leave_nationals);
    //     $count_leave_nationals = count($leave_nationals);

    //     $notional_holiday = [
    //                 'leave_id' => 0,
    //                 'leave_name' => $lang == "en" ? "Official holidays" : "الاجازات الرسميه",
    //                 'leave_count' => $count_all_leave_nationals,
    //                 'leave_paid' => $count_all_leave_nationals,
    //                 'leave_unpaid' => 0,
    //                 'my_leave_count' => $count_leave_nationals,
    //                 'my_leave_paid' => 0,
    //                 'my_leave_unpaid' => 0,
    //             ];

    //     $employee_leaves->push($notional_holiday);
    //     $employee_requests = LeaveRequest::with('leaveTypes')->where('employee_id', $employee->id)->orderBy('date', 'desc')->take(5)->get();
    //     $data = ['leaves' => $employee_leaves, 'requests' => $employee_requests];

    //     return ResponseWithSuccessData($lang, $data, 1);
    // }


    public function EmployeeLeaves($request)
    {
        $lang = $request->header('lang', 'en');

        $validator = Validator::make($request->all(), [
            'employee_id' => 'nullable|exists:employees,id'
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $employee = Auth::guard('employee')->user();

        if ($employee->hasPermissionTo('create leave_requests_for_all_employees', 'employee')) {
            $employee_id = $request->employee_id ?? $employee->id;
        } else {
            $employee_id = $employee->id;
        }

        if (!$employee_id) {
            return respondError(
                $lang == 'en'
                    ? 'Sorry, We did not find employee'
                    : 'عفوا لا يوجد موظف ',
                400,
                $lang == 'en'
                    ? ['Sorry, We did not find employee to make search for it']
                    : ['عفوا لا يوجد موظف ليتم البحث عليه']
            );
        }

        $employee = Employee::find($employee_id);

        // Load all leave types with ONLY the latest setting & position
        $leave_types = LeaveType::with([
            'leaveSettings' => function ($q) use ($employee) {
                $q->where('country_id', $employee->country_id)
                    ->whereHas('leaveSettingPositions', function ($sub) use ($employee) {
                        $sub->where('position_id', $employee->position_id);
                    })
                    ->orderByDesc('id')
                    ->limit(1) // take only last leaveSetting
                    ->with([
                        'leaveSettingPositions' => function ($q) use ($employee) {
                            $q->where('position_id', $employee->position_id)
                                ->orderByDesc('id')
                                ->limit(1); // take only last position
                        }
                    ]);
            }
        ])->get();

        if ($leave_types->isEmpty()) {
            return respondError(
                $lang == 'en' ? 'No leave types found.' : 'لا يوجد أنواع إجازات.',
                400
            );
        }

        // Build the full structure with employee-specific data
        $employee_leaves = $leave_types->map(function ($leave_type) use ($employee) {

            $last_setting = $leave_type->leaveSettings->first(); // latest setting
            $employee_leave = EmployeeLeave::where([
                'employee_id'   => $employee->id,
                'leave_type_id' => $leave_type->id,
            ])->first();

            return [
                'leave_id'        => $leave_type->id,
                'leave_name'      => $leave_type->name,
                'leave_details'   => $leave_type->details,
                'leave_image'     => $leave_type->image,
                'leave_count'     => $last_setting->day_count ?? 0,
                'leave_paid'      => $last_setting->day_paid ?? 0,
                'leave_unpaid'    => $last_setting->day_unpaid ?? 0,
                'my_leave_count'  => $employee_leave->day_count ?? 0,
                'my_leave_paid'   => $employee_leave->day_paid ?? 0,
                'my_leave_unpaid' => $employee_leave->day_unpaid ?? 0,
            ];
        });

        // Get last 5 leave requests
        $employee_requests = LeaveRequest::with('leaveTypes')
            ->where('employee_id', $employee->id)
            ->orderBy('date', 'desc')
            ->get();

        $data = [
            'leaves'   => $employee_leaves,
            'requests' => $employee_requests,
        ];

        return ResponseWithSuccessData($lang, $data, 1);
    }



    public function EmployeeLeavesMonth($employee_id, $from = null, $to = null)
    {
        $lang =  $this->lang;
        // try {
        // $validateData = Validator::make($request->all(), [
        //     'leave_type_id' => [
        //         'nullable',
        //         Rule::exists('leave_types', 'id')->whereNull('deleted_at')
        //     ],
        //     'leave_request_id' => [
        //         'nullable',
        //         Rule::exists('leave_requests', 'id')->whereNull('deleted_at')
        //     ],
        //     'employee_id' => [
        //         'nullable',
        //         Rule::exists('employees', 'id')->whereNull('deleted_at')
        //     ],
        //     'position_id' => [
        //         'nullable',
        //         Rule::exists('positions', 'id')->whereNull('deleted_at')
        //     ],
        //     'from' => 'nullable|date',
        //     'to' => 'nullable|date'
        // ]);

        // if ($validateData->fails()) {
        //     return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validateData->errors());
        // }

        $check_employee = Employee::where('id', $employee_id)->first();
        if (!$check_employee) {
            return respondError(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This employee is not existing any more'] : ['هذا الموظف غير موجود']);
        }

        // $employee_leave_log = EmployeeLeaveLog::with(['employees', 'leaveTypes', 'positions', 'leaveRequests']);
        $employee_leave_log = EmployeeLeaveLog::with(['employees']);

        // if ($request->has('leave_type_id')) {
        //     $employee_leave_log = $employee_leave_log->where('leave_type_id', $request->leave_type_id);
        // }
        if (isset($employee_id)) {
            $employee_leave_log = $employee_leave_log->where('employee_id', $employee_id);
        }
        // if ($request->has('position_id')) {
        //     $employee_leave_log = $employee_leave_log->where('position_id', $request->position_id);
        // }
        // if ($request->has('leave_request_id')) {
        //     $employee_leave_log = $employee_leave_log->where('leave_request_id', $request->leave_request_id);
        // }
        if (isset($from)) {
            $employee_leave_log = $employee_leave_log->where('from', '>=', $from);
        }
        if (isset($to)) {
            $employee_leave_log = $employee_leave_log->where('to', '<=', $to);
        }

        $data = $employee_leave_log->get();
        return  $data;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');

        // Validate filters
        $validateData = Validator::make($request->all(), [
            'leave_type_id' => [
                'nullable',
                Rule::exists('leave_types', 'id')->whereNull('deleted_at')
            ],
            'employee_id' => [
                'nullable',
                Rule::exists('employees', 'id')->whereNull('deleted_at')
            ],
            'department_id' => [
                'nullable',
                Rule::exists('departments', 'id')->whereNull('deleted_at')
            ],
            'from' => 'nullable|date',
            'to'   => 'nullable|date',
            'status' => 'nullable|in:pending,approved,rejected,1,0,2', // example
        ]);

        if ($validateData->fails()) {
            return respondError(
                ($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'),
                400,
                $validateData->errors()
            );
        }

        // Build main query
        $leave_requests = LeaveRequest::with(['employees.department', 'employees.position', 'leaveTypes', 'leaveRequestAgreements']);

        if ($request->has('leave_type_id')) {
            $leave_requests->where('leave_type_id', $request->leave_type_id);
        }
        if ($request->has('employee_id')) {
            $leave_requests->where('employee_id', $request->employee_id);
        }
        if ($request->has('department_id')) {
            $leave_requests->whereHas('employees', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }
        if ($request->has('from')) {
            $leave_requests->where('from', '>=', $request->from);
        }
        if ($request->has('to')) {
            $leave_requests->where('to', '<=', $request->to);
        }
        if ($request->has('status')) {
            $leave_requests->where('status', $request->status);
        }

        $leave_requests->with(['leaveTypes.employees_leaves']);

        // Get current employee
        $employee = auth('employee')->user();
        $child_employees = getSupervisedEmployees($employee->id);

        $myRequests = collect();
        $employeeRequests = collect();

        // Determine access level
        // if ($employee->hasRole('HR_Manager', 'employee', 'superAdmin')) {
        if ($employee->hasRole('HR_Manager', 'employee')) {
            // HR Manager → all others + self
            $myRequests = (clone $leave_requests)->where('employee_id', $employee->id);
            $employeeRequests = (clone $leave_requests)->where('employee_id', '!=', $employee->id);
        } elseif ($child_employees && $child_employees->count() > 0) {
            // Supervisor → own + supervised employees
            $childEmployeeIds = $child_employees->pluck('id')->toArray();
            $employeeRequests = (clone $leave_requests)->whereIn('employee_id', $childEmployeeIds);
            $myRequests = (clone $leave_requests)->where('employee_id', $employee->id);
        } else {
            // Regular employee → only own requests
            $myRequests = (clone $leave_requests)->where('employee_id', $employee->id);
            $employeeRequests = LeaveRequest::query()->whereRaw('1 = 0');
        }

        // $myRequestsData = $myRequests->get();

        // $myRequestsData->transform(function ($item) use ($lang) {
        //     if (!empty($item->leaveRequestAgreements) && isset($item->leaveRequestAgreements[0]->agreement)) {
        //         $item->leaveRequestAgreements[0]->agree =
        //             $item->leaveRequestAgreements[0]->agreement == 1
        //                 ? ($lang == 'en' ? 'accept' : 'موافق')
        //                 : ($lang == 'en' ? 'reject' : 'رفض');
        //     }
        //     if (!empty($item->leaveRequestAgreements) && isset($item->leaveRequestAgreements[0]->position_id)) {
        //         // dd(Position::where('id', $item->leaveRequestAgreements[0]->position_id)->first());
        //         $item->leaveRequestAgreements[0]->position_name = Position::find($item->leaveRequestAgreements[0]->position_id)?->name;
        //     }
        //     return $item;
        // });
        // return $myRequestsData;
        // Paginate or get all
        $myRequestsData = paginateOrGetAll($myRequests, $request, []);
        $employeeRequestsData = paginateOrGetAll($employeeRequests, $request, []);
        if ($myRequestsData['data']) {
            $myRequestsData['data']->transform(function ($item) use ($lang) {
                $item->employees->is_active = $item->employees->is_active == 0 ? ($lang == 'en' ? 'Inactive' : 'غير نشط') : ($lang == 'en' ? 'Active' : 'نشط');
                return $item;
            });
        }
        if ($employeeRequestsData['data']) {
            $employeeRequestsData['data']->transform(function ($item) use ($lang) {
                $item->employees->is_active = $item->employees->is_active == 0 ? ($lang == 'en' ? 'Inactive' : 'غير نشط') : ($lang == 'en' ? 'Active' : 'نشط');
                return $item;
            });
        }
        // Response
        $data['data'] = [
            'my' => !empty($myRequestsData['data'] ?? null) ? $myRequestsData['data'] : null,
            'employees' =>  !empty($employeeRequestsData['data']) ? $employeeRequestsData['data'] : null,
        ];
        $data['meta'] = $myRequestsData['meta'] ?? null;

        return ResponseWithSuccessDataPaginated($lang, $data, 1);
    }

    public function show($id)
    {
        try {
            $validateData = Validator::make(
                ['id' => $id],
                [
                    'id' => [
                        'required',
                        Rule::exists('leave_requests', 'id')->whereNull('deleted_at'),
                    ],
                ]
            );

            if ($validateData->fails()) {
                return respondError(
                    ($this->lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'),
                    404,
                    $validateData->errors()
                );
            }

            $check_leave_type = LeaveRequest::where('id', $id)->first();
            // if(!$check_leave_type){
            //     return respondError(($this->lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $this->lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
            // }

            $supervisor_id = $check_leave_type->employees->supervisor_id;

            $leave_type = LeaveRequest::with([
                'leaveTypes',
                'employees',
                'leaveRequestAgreements',
                'position'
            ])->where('id', $id)->first();
            // return $leave_type = LeaveRequest::with(['leaveTypes', 'employees', 'leaveRequestAgreements'])->with('leaveRequestAgreements', function($query) use($supervisor_id){
            //     $query->where('agreement_by', $supervisor_id);
            // })->where('id',$id)->first();
            // $leave_type->makeHidden(['name'])->makeVisible(['name_ar', 'name_en']);
            $leave_type->employees->makeHidden(['kitchen_info']);
            $leave_type->leaveRequestAgreements->map(function ($agreement){
                $employee = Employee::with('position')->where('id', $agreement->agreement_by)->first();
                $agreement->position = $employee?->position == 'HR' ? $employee?->position->name : 'Manager';
            });
            return ResponseWithSuccessData($this->lang, $leave_type, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function add(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        // try {
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }

        // $validateData = Validator::make($request->all(), [
        //     'leave_type_id' => [
        //         'required',
        //         Rule::exists('leave_types', 'id')->whereNull('deleted_at')
        //     ],
        //     'employee_id' => [
        //         'nullable',
        //         Rule::exists('employees', 'id')->whereNull('deleted_at')
        //     ],
        //     'from' => 'required|date|date_format:Y-m-d',
        //     'to' => 'required|date|date_format:Y-m-d|after_or_equal:from',
        //     // 'leave_count' => 'required|integer',
        //     'resone' => 'string|nullable',
        //     'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        // ]);
        // if ($validateData->fails()) {
        //     return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validateData->errors());
        // }

        $rules = [
            'leave_type_id' => [
                'required',
                Rule::exists('leave_types', 'id')->whereNull('deleted_at')
            ],
            'employee_id' => [
                'nullable',
                Rule::exists('employees', 'id')->whereNull('deleted_at')
            ],
            'from' => 'required|date|date_format:Y-m-d',
            'to' => 'required|date|date_format:Y-m-d|after_or_equal:from',
            'resone' => 'string|nullable',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];

        $employee = Employee::find($request->employee_id ?? auth('employee')->id());

        $flag = LeaveSetting::where('leave_type_id', $request->leave_type_id)
            ->where('country_id', $employee->country_id)
            ->first();

        if ($flag?->upload_certificate === 'yes') {
            $rules['file'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:2048';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $get_employee_request = LeaveRequest::where('employee_id', $request->employee_id)->where('from', $request->from)->first();
        if ($get_employee_request) {
            return respondError(($lang == 'en' ? 'Allready make request' : 'الطلب موجود بالفعل'), 400, $lang == 'en' ? ['You have applied on this date before.'] : ['لقد قمت بتقديم طلب فى هذا التاريخ من قبل']);
        }

        $employee = auth('employee')->user();

        // return $employee->employeeShiftDetails;

        if (isset($request->employee_id) && $employee->id != $request->employee_id) {
            $get_employee = Employee::where('id', $request->employee_id)->first();
            if ($get_employee) {
                $employee_id = $get_employee->id;
                $position_id = $get_employee->position_id;
            }
        } else {
            $employee_id = $employee->id;
            $position_id = $employee->position_id;
        }
        if ($position_id == null) {
            return respondError(
                $lang == 'en' ? 'Sorry, We did not found employee' : 'عفوا لا يوجد موظف ',
                400,
                $lang == 'en' ? ['Sorry, We did not found employee position to make search for it'] : ['عفوا لا يوجد مسمي موظف ليتم البحث عليه']
            );
        }

        // $employee_role = $employee->getRoleNames();
        // $get_employee = Employee::where('id', $request->employee_id)->first();
        // if($employee->id == $get_employee->id){
        //     $employee_id = $employee->id;
        //     $position_id = $employee->position_id;
        // }else{
        //     $employee_id = $get_employee->id;
        //     $position_id = $get_employee->position_id;
        // }

        // if($get_employee->id != $employee->id && $employee_role == "employee"){
        //     return respondError(($lang == 'en' ? ['You can not make request'] : ['لا يمكنك طلب الاجازه']), 400, $lang == 'en' ? ['You can not make request to another employee.'] : ['لا يمكنك طلب الاجازة لغيرك']);
        // }


        $check_leaves = EmployeeLeaveLog::where('employee_id', $employee_id)
            ->whereDate('from', '<=', $request->from)
            ->whereDate('to', '>=', $request->to)
            ->exists();
        if ($check_leaves) {
            return respondError(($lang == 'en' ? ['You can not make request'] : ['لا يمكنك طلب الاجازه']), 400, $lang == 'en' ? ['You cannot request leave because you are already on leave.'] : ['لا يمكنك طلب الاجازة لانك فى اجازه بالفعل']);
        }

        $from_date = Carbon::parse($request->from);
        $to_date = Carbon::parse($request->to);
        $leave_count = $to_date->diffInDays($from_date);
        // $to_date = $from_date->copy()->addDays($request->leave_count - 1);

        $add_leave = new LeaveRequest();
        $add_leave->leave_type_id = $request->leave_type_id;
        $add_leave->employee_id = $employee_id;
        $add_leave->date = date('Y-m-d');
        $add_leave->from = $request->from;
        $add_leave->to = $request->to;
        $add_leave->leave_count = $leave_count;
        $add_leave->resone = $request->resone;
        $add_leave->position_id = $position_id;
        $add_leave->created_by = $employee_id;
        $add_leave->save();

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $file = $request->file('file');
            UploadFile('images/leave_setting', 'file', $add_leave, $file);
        }

        $hr_service_id = $this->hrServiceService->getByKey('LeaveRequest');
        $data = [
            'employee_id' => $employee->id, // Assuming `users` table for employees
            'hr_service_id' => $hr_service_id, // Assuming `hr_services` table
            'request_id' => $add_leave->id,
            'status' => 'pending',
        ];
        $data['created_by'] =  authActionSave()['by'];
        $data['created_by_type'] = authActionSave()['type'];
        $hrRequest = $this->hrRequestService->create($data);

        return ResponseWithSuccessData($lang, $add_leave, 1);
        // } catch (\Exception $e) {
        //     return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        // }
    }

    public function edit(Request $request, $id)
    {
        // try {
        $lang =  $request->header('lang', 'en');
        $validateData = Validator::make($request->all(), [
            // 'id' => 'required|exists:leave_requests,id',
            // // 'leave_type_id' => 'required|exists:leave_types,id',
            // // 'employee_id' => 'required|exists:employees,id',
            // 'leave_type_id' => [
            //     'required',
            //     Rule::exists('leave_types', 'id')->whereNull('deleted_at')
            // ],
            // 'employee_id' => [
            //     'required',
            //     Rule::exists('employees', 'id')->whereNull('deleted_at')
            // ],
            // 'date' => 'required|date',
            // 'from' => 'required|date',
            // 'leave_count' => 'required|integer',
            // 'resone' => 'string|nullable'
            'leave_type_id' => [
                'required',
                Rule::exists('leave_types', 'id')->whereNull('deleted_at')
            ],
            'employee_id' => [
                'nullable',
                Rule::exists('employees', 'id')->whereNull('deleted_at')
            ],
            'from' => 'required|date|date_format:Y-m-d',
            'to' => 'required|date|date_format:Y-m-d|after_or_equal:from',
            // 'leave_count' => 'required|integer',
            'resone' => 'string|nullable',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($validateData->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validateData->errors());
        }

        $employee = auth('employee')->user();
        if (isset($request->employee_id) && $employee->id != $request->employee_id) {
            $get_employee = Employee::where('id', $request->employee_id)->first();
            if ($get_employee) {
                $employee_id = $get_employee->id;
                $position_id = $get_employee->position_id;
            }
        } else {
            $employee_id = $employee->id;
            $position_id = $employee->position_id;
        }
        if ($position_id == null) {
            return respondError(
                $lang == 'en' ? 'Sorry, We did not found employee' : 'عفوا لا يوجد موظف ',
                400,
                $lang == 'en' ? ['Sorry, We did not found employee position to make search for it'] : ['عفوا لا يوجد مسمي موظف ليتم البحث عليه']
            );
        }

        $date = date('Y-m-d');

        $leave_request = LeaveRequest::find($id);
        if (!$leave_request) {
            return respondError(
                $lang == 'en' ? 'Sorry, We did not found request' : 'عفوا لا يوجد طلب ',
                400,
                $lang == 'en' ? ['Sorry, We did not found request to make search for it'] : ['عفوا لا يوجد طلب ليتم البحث عليه']
            );
        }

        if ($leave_request->status != "pending") {
            // return  RespondWithBadRequestNotHavePermeation();
            return respondError(
                $lang == 'en' ? 'It cannot be deleted or modified because operations have occurred on it' : 'لا يمكن حذفه أو تعديله لأنه تم إجراء عمليات عليه ',
                400,
                $lang == 'en' ? ['You do not have permission to modified this request'] : ['ليس لديك صلاحيه لتعديل هذا الطلب']
            );
        }

        $check_leaves = EmployeeLeaveLog::where('employee_id', $employee->id)
            ->whereDate('from', '<=', $request->from)
            ->whereDate('to', '>=', $request->to)
            ->exists();
        if ($check_leaves) {
            return respondError(($lang == 'en' ? ['You can not make request'] : ['لا يمكنك طلب الاجازه']), 400, $lang == 'en' ? ['You cannot request leave because you are already on leave.'] : ['لا يمكنك طلب الاجازة لانك فى اجازه بالفعل']);
        }

        $from_date = Carbon::parse($request->from);
        $to_date = Carbon::parse($request->to);
        $leave_count = $to_date->diffInDays($from_date);

        $leave_request->leave_type_id = $request->leave_type_id;
        // $leave_request->employee_id = $request->employee_id;
        $leave_request->from = $request->from;
        $leave_request->to = $request->to;
        $leave_request->leave_count = $leave_count;
        $leave_request->resone = $request->resone;
        $leave_request->modified_by = $employee->id;
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $file = $request->file('file');
            UploadFile('images/leave_setting', 'file', $leave_request, $file);
        }
        // $leave_request->file = $leave_request->file;
        $leave_request->save();

        return ResponseWithSuccessData($lang, $leave_request, 1);
        // } catch (\Exception $e) {
        //     return RespondWithBadRequestData($lang, 2);
        // }
    }


    public function delete(Request $request, $id)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $employee = auth('employee')->user();
            $date = date('Y-m-d');

            $leave_request = LeaveRequest::find($request->id);
            if (!$leave_request) {
                return respondError(($this->lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $this->lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            if ($leave_request->status != "pending") {
                return respondError(($this->lang == 'en' ? 'It cannot be deleted or modified because operations have occurred on it' : 'لا يمكن حذفه أو تعديله لأنه تم إجراء عمليات عليه'), 404, $this->lang == 'en' ? ['You do not have permission to delete this request'] : ['ليس لديك صلاحيه لحذف هذا الطلب']);
            }

            // if ($leave_request->from <= $date) {
            //     return  RespondWithBadRequestNotDate($lang, 9);
            // }

            $leave_request->deleted_by = $employee->id;
            $leave_request->save();
            $hr_request = HRRequest::where('request_id', $leave_request->id)->where('hr_service_id', 1)->delete();
            $leave_request->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Send notification to employee when leave request status changes
     */
    private function sendLeaveRequestNotification($leave_request, $status, $lang)
    {
        try {
            $employee = Employee::where('id', $leave_request->employee_id)
                ->where('status', 'active')
                ->whereNotNull('device_token')
                ->first();

            if (!$employee || !$employee->device_token) {
                Log::info("No notification sent - Employee not found or no device token for employee ID: " . $leave_request->employee_id);
                return false;
            }

            $leaveTypeName = $leave_request->leaveTypes->name ?? 'Leave';

            if ($status === 'approved') {
                $title_ar = 'تمت الموافقة على طلب الإجازة';
                $title_en = 'Leave Request Approved';
                $description_ar = "تمت الموافقة على طلب إجازة {$leaveTypeName} من {$leave_request->from} لمدة {$leave_request->leave_count} يوم";
                $description_en = "Your {$leaveTypeName} request from {$leave_request->from} for {$leave_request->leave_count} days has been approved";
                $notification_type = 'leave_approved';
            } else {
                $title_ar = 'تم رفض طلب الإجازة';
                $title_en = 'Leave Request Rejected';
                $description_ar = "تم رفض طلب إجازة {$leaveTypeName} من {$leave_request->from} لمدة {$leave_request->leave_count} يوم";
                $description_en = "Your {$leaveTypeName} request from {$leave_request->from} for {$leave_request->leave_count} days has been rejected";
                $notification_type = 'leave_rejected';
            }

            // Generate URL for the notification
            $fullUrl = url()->current();
            $apiBaseUrl = \Illuminate\Support\Str::before($fullUrl, '/api');
            $url = $apiBaseUrl . '/leave-requests/' . $leave_request->id;

            $notification = send_push_notification(
                $employee->device_token,
                $description_ar,
                $description_en,
                $title_ar,
                $title_en,
                $notification_type,
                $employee->id,
                Auth::guard('employee')->id(),
                $leave_request->id,
                $lang,
                5,
                $url
            );
        } catch (\Exception $e) {
            Log::error('Error sending leave request notification: ' . $e->getMessage());
            return false;
        }
    }
}
