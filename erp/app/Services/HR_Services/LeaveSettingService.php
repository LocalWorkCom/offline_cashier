<?php

namespace App\Services\HR_Services;

use App\Models\LeaveSetting;
use App\Models\LeaveSettingPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LeaveSettingService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function index(Request $request)
    {
        $lang = app()->getLocale();
        try {
            return $data = LeaveSetting::query()->with(['countries', 'leaveTypes']);
        } catch (\Exception $e) {
            Log::error('Error fetching color: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return respondError(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function show($id)
    {
        try {
            $leave_setting = LeaveSetting::with(['countries', 'leaveTypes'])->findOrFail($id);
            return ResponseWithSuccessData($this->lang, $leave_setting, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function show_all($column_name, $column_val)
    {
        try {
            $leave_settings = LeaveSetting::where($column_name, $column_val)->get();
            return ResponseWithSuccessData($this->lang, $leave_settings, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function add(Request $request)
    {
        try {
            $lang = app()->getLocale();

            $validateData = Validator::make($request->all(), [
                // 'leave_type_id' => 'required|exists:leave_types,id',
                // 'country_id' => 'required|exists:countries,id',
                'leave_type_id' => [
                    'required',
                    Rule::exists('leave_types', 'id')->whereNull('deleted_at')
                ],
                'country_id' => [
                    'required',
                    Rule::exists('countries', 'id')->whereNull('deleted_at')
                ],
                'day_count' => 'required|integer',
                'day_paid' => 'required|integer',
                'day_unpaid' => 'required|integer',
                'deduction_value' => 'required|numeric',
                'leave_type' => 'required',
                'within_month' => 'required|integer',
                // 'file' => 'mimes:jpeg,png,jpg,gif,svg'
            ]);


            if ($validateData->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validateData->errors());
            }

            $check_leave_existing = LeaveSetting::where('leave_type_id', $request->leave_type_id)->where('country_id', $request->country_id)->exists();
            if ($check_leave_existing) {
                return respondError(($lang == 'en' ? ['Data is existing'] : ['موجود بالفعل']), 400, $lang == 'en' ? ['This data is already existing in our system'] : ['هذه البيانات موجودة بالفعل']);
            }

            $file = $request->file('file');
            $craeted = authActionSave();
            $created_by = $craeted['by'];
            $created_by_type = $craeted['type'];

            $leave_setting = new LeaveSetting();
            $leave_setting->leave_type_id = $request->leave_type_id;
            $leave_setting->country_id = $request->country_id;
            $leave_setting->day_count = $request->day_count;
            $leave_setting->day_paid = $request->day_paid;
            $leave_setting->day_unpaid = $request->day_unpaid;
            $leave_setting->deduction_value = $request->deduction_value;
            $leave_setting->leave_type = $request->leave_type;
            $leave_setting->within_month = $request->within_month;
            $leave_setting->upload_certificate = $request->upload_certificate;
            $leave_setting->transfer_leave = $request->transfer_leave;
            $leave_setting->created_by = $created_by;
            $leave_setting->created_by_type = $created_by_type;
            $leave_setting->save();

            if ($request->hasFile('file')) {
                UploadFile('images/leave_setting', 'file', $leave_setting, $file);
            }

            $check_leave_existing = LeaveSetting::where('id', $leave_setting->id)->with(['countries', 'leaveTypes'])->first();

            return ResponseWithSuccessData($this->lang, $check_leave_existing, 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $lang = app()->getLocale();

            $validateData = Validator::make($request->all(), [
                // 'leave_type_id' => 'required|exists:leave_types,id',
                // 'country_id' => 'required|exists:countries,id',
                'leave_type_id' => [
                    'required',
                    Rule::exists('leave_types', 'id')->whereNull('deleted_at')
                ],
                'country_id' => [
                    'required',
                    Rule::exists('countries', 'id')->whereNull('deleted_at')
                ],
                'day_count' => 'required|integer',
                'day_paid' => 'required|integer',
                'day_unpaid' => 'required|integer',
                'deduction_value' => 'required|numeric',
                'leave_type' => 'required',
                'within_month' => 'required|integer',
                //'file' => 'mimes:jpeg,png,jpg,gif,svg'
            ]);

            if ($validateData->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validateData->errors());
            }

            $leave_setting = LeaveSetting::find($id);
            if (!$leave_setting) {
                return respondError(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
            }

            $check_leave_existing = LeaveSetting::where('leave_type_id', $request->leave_type_id)->where('country_id', $request->country_id)->where('id', '!=', $request->id)->exists();
            if ($check_leave_existing) {
                return respondError(($lang == 'en' ? ['Data is existing'] : ['موجود بالفعل']), 400, $lang == 'en' ? ['This data is already existing in our system'] : ['هذه البيانات موجودة بالفعل']);
            }

            $file = $request->file('file');

            $craeted = authActionSave();
            $modified_by = $craeted['by'];
            $modified_by_type = $craeted['type'];

            $leave_setting = LeaveSetting::findOrFail($request->id);
            $leave_setting->leave_type_id = $request->leave_type_id;
            $leave_setting->country_id = $request->country_id;
            $leave_setting->day_count = $request->day_count;
            $leave_setting->day_paid = $request->day_paid;
            $leave_setting->day_unpaid = $request->day_unpaid;
            $leave_setting->deduction_value = $request->deduction_value;
            $leave_setting->leave_type = $request->leave_type;
            $leave_setting->within_month = $request->within_month;
            $leave_setting->upload_certificate = $request->upload_certificate;
            $leave_setting->transfer_leave = $request->transfer_leave;
            $leave_setting->modified_by = $modified_by;
            $leave_setting->modified_by_type = $modified_by_type;
            $leave_setting->save();

            if ($request->hasFile('file')) {
                UploadFile('images/leave_setting', 'file', $leave_setting, $file);
            }

            $check_leave_existing = LeaveSetting::where('id', $leave_setting->id)->with(['countries', 'leaveTypes'])->first();

            return ResponseWithSuccessData($this->lang, $check_leave_existing, 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function delete(Request $request, $id)
    {
        if (isset($request->lang)) {
            $lang = $request->lang;
        } else {
            $lang = app()->getLocale();
        }
        try {

            $check_leave = LeaveSetting::where('id', $request->id)->first();
            if (!$check_leave) {
                return respondError(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
            }

            $check_leave_setting = leaveSettingPosition::where('leave_setting_id', $request->id)->exists();
            if ($check_leave_setting) {
                return respondError(($lang == 'en' ? ['Can nott delete this item'] : ['لا تسطيع الحذف']), 400, $lang == 'en' ? ['Cannot delete this item because related with leaves setting position'] : ['لا يمكن حذف هذا العنصر لأنه مرتبط بتعيين أنواع الإجازات حسب المسمى الوظيفي']);
            }

            $craeted = authActionSave();
            $deleted_by = $craeted['by'];
            $deleted_by_type = $craeted['type'];
            $check_leave->deleted_by = $deleted_by;
            $check_leave->deleted_by_type = $deleted_by_type;
            $check_leave->save();
            $delete_color = $check_leave->delete();

            return RespondWithSuccessRequest($this->lang, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching color: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            return respondError(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }
}
