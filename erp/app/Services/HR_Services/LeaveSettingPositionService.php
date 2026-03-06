<?php


namespace App\Services\HR_Services;

use App\Models\LeaveSettingPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
class LeaveSettingPositionService
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
            return $data = LeaveSettingPosition::query()->with(['leaveSettings.countries', 'leaveSettings.leaveTypes', 'positions']);
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
            $leave_setting = LeaveSettingPosition::with(['leaveSettings.countries', 'leaveSettings.leaveTypes', 'positions'])->findOrFail($id);
            return ResponseWithSuccessData($this->lang, $leave_setting, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function add(Request $request)
    {
        $lang = app()->getLocale();
        try {
            $validateData = Validator::make($request->all(), [
                // 'leave_setting_id' => 'required|exists:leave_settings,id',
                // 'position_id' => 'required|exists:positions,id',
                'leave_setting_id' => [
                    'required',
                    Rule::exists('leave_settings', 'id')->whereNull('deleted_at')
                ],
                'position_id' => [
                    'required',
                    Rule::exists('positions', 'id')->whereNull('deleted_at')
                ],
                'day_count' => 'required|integer',
                'split_max' => 'integer'
            ]);

            if ($validateData->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validateData->errors());
            }

            $check_leave_existing = LeaveSettingPosition::where('leave_setting_id', $request->leave_setting_id)->where('position_id', $request->position_id)->exists();
            if ($check_leave_existing) {
                return respondError(($lang == 'en' ? ['Data is existing'] : ['موجود بالفعل']), 400, $lang == 'en' ? ['This data is already existing in our system'] : ['هذه البيانات موجودة بالفعل']);
            }

            $craeted = authActionSave();
            $created_by = $craeted['by'];
            $created_by_type = $craeted['type'];

            $leave_setting = new LeaveSettingPosition();
            $leave_setting->leave_setting_id = $request->leave_setting_id;
            $leave_setting->position_id = $request->position_id;
            $leave_setting->day_count = $request->day_count;
            $leave_setting->leave_pattern = $request->leave_pattern;
            $leave_setting->split_max = $request->leave_pattern == "consecutive" ? 0 : $request->split_max;
            $leave_setting->hr_approve = "yes";
            $leave_setting->higher_position_approve = json_encode(array_map('intval', $request->higher_position_approve ?? []));
            $leave_setting->higher_position_setting = $request->higher_position_setting;
            $leave_setting->roles_assign = json_encode(array_map('intval', $request->roles_assign ?? []));
            $leave_setting->roles_view = json_encode(array_map('intval', $request->roles_view ?? []));
            $leave_setting->created_by = $created_by;
            $leave_setting->created_by_type = $created_by_type;
            $leave_setting->save();

            $check_leave_existing = LeaveSettingPosition::where('id', $leave_setting->id)->with(['leaveSettings.countries', 'leaveSettings.leaveTypes', 'positions'])->first();
            return ResponseWithSuccessData($this->lang, $check_leave_existing, 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function edit(Request $request, $id)
    {
        $lang = app()->getLocale();
        try {
            $validateData = Validator::make($request->all(), [
                // 'leave_setting_id' => 'required|exists:leave_settings,id',
                // 'position_id' => 'required|exists:positions,id',
                'leave_setting_id' => [
                    'required',
                    Rule::exists('leave_settings', 'id')->whereNull('deleted_at')
                ],
                'position_id' => [
                    'required',
                    Rule::exists('positions', 'id')->whereNull('deleted_at')
                ],
                'day_count' => 'required|integer',
                'split_max' => 'integer'
            ]);

            if ($validateData->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validateData->errors());
            }

            $leave_setting = LeaveSettingPosition::find($id);
            if (!$leave_setting) {
                return respondError(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
            }

            $check_leave_existing = LeaveSettingPosition::where('leave_setting_id', $request->leave_setting_id)->where('position_id', $request->position_id)->where('id', '!=', $request->id)->exists();
            if ($check_leave_existing) {
                return respondError(($lang == 'en' ? ['Data is existing'] : ['موجود بالفعل']), 400, $lang == 'en' ? ['This data is already existing in our system'] : ['هذه البيانات موجودة بالفعل']);
            }

            $craeted = authActionSave();
            $modified_by = $craeted['by'];
            $modified_by_type = $craeted['type'];

            $leave_setting = LeaveSettingPosition::findOrFail($request->id);
            $leave_setting->leave_setting_id = $request->leave_setting_id;
            $leave_setting->position_id = $request->position_id;
            $leave_setting->day_count = $request->day_count;
            $leave_setting->leave_pattern = $request->leave_pattern;
            $leave_setting->split_max = $request->leave_pattern == "consecutive" ? 0 : $request->split_max;
            $leave_setting->hr_approve = "yes";
            $leave_setting->higher_position_approve = json_encode(array_map('intval', $request->higher_position_approve ?? []));
            $leave_setting->higher_position_setting = $request->higher_position_setting;
            $leave_setting->roles_assign = json_encode(array_map('intval', $request->roles_assign ?? []));
            $leave_setting->roles_view = json_encode(array_map('intval', $request->roles_view ?? []));
            $leave_setting->modified_by = $modified_by;
            $leave_setting->modified_by_type = $modified_by_type;
            $leave_setting->save();

            $check_leave_existing = LeaveSettingPosition::where('id', $leave_setting->id)->with(['leaveSettings.countries', 'leaveSettings.leaveTypes', 'positions'])->first();
            return ResponseWithSuccessData($this->lang, $check_leave_existing, 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function delete(Request $request, $id)
    {
        $lang = app()->getLocale();
        try {

            //check if employee has leave to this setting

            $check_leave = LeaveSettingPosition::find($request->id);
            if (!$check_leave) {
                return respondError(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
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
            return respondError(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }
    
}
