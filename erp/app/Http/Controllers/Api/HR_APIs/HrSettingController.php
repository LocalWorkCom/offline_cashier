<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\HrSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HrSettingController extends Controller
{
    /**
     * Get all HR settings grouped by branch
     */
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $settings = HrSetting::with('branch')->get();

        return ResponseWithSuccessData($lang, $settings, 1);
    }
    /**
     * Get HR settings for a specific branch
     */
    public function getByBranch(Request $request, $branchId)
    {
        $lang = $request->header('lang', 'ar');

        $branch = Branch::find($branchId);

        if (!$branch) {
            $message = $lang == 'en' ? 'Branch not found' : 'الفرع غير موجود';
            return respondError($message, 404);
        }

        $setting = HrSetting::where('branch_id', $branchId)->first();

        if (!$setting) {
            $message = $lang == 'en' ? 'HR setting not found for this branch' : 'لم يتم العثور على إعدادات الموارد البشرية لهذا الفرع';
            return respondError($message, 404);
        }

        return ResponseWithSuccessData($lang, $setting, 1);
    }

    /**
     * Update HR settings for a specific branch
     */
    public function updateByBranch(Request $request, $branchId)
    {
        $lang = $request->header('lang', 'ar');

        $branch = Branch::find($branchId);

        if (!$branch) {
            $message = $lang == 'en' ? 'Branch not found' : 'الفرع غير موجود';
            return respondError($message, 404);
        }

        $validator = Validator::make($request->all(), [
            'duplicate_punch_threshold_minutes' => 'required|integer|min:1|max:120',
        ], [], [
            'duplicate_punch_threshold_minutes' => __('validation.attributes.duplicate_punch_threshold_minutes'),
        ]);

        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }

        $setting = HrSetting::updateOrCreate(
            ['branch_id' => $branchId],
            [
                'duplicate_punch_threshold_minutes' => $request->duplicate_punch_threshold_minutes,
            ]
        );

        return ResponseWithSuccessData($lang, $setting, 1);
    }


    /**
     * Create new HR setting for a branch
     */
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|integer|exists:branches,id',
            'duplicate_punch_threshold_minutes' => 'required|integer|min:1|max:120',
        ], [], [
            'branch_id' => __('validation.attributes.branch_id'),
            'duplicate_punch_threshold_minutes' => __('validation.attributes.duplicate_punch_threshold_minutes'),
        ]);

        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }

        // Check if setting already exists for this branch
        $existingSetting = HrSetting::where('branch_id', $request->branch_id)->first();

        if ($existingSetting) {
            $message = $lang == 'en' ? 'HR setting already exists for this branch. Use update endpoint to modify.' : 'إعدادات الموارد البشرية موجودة بالفعل لهذا الفرع. استخدم نقطة التحديث للتعديل.';
            return respondError($message, 400);
        }

        $setting = HrSetting::create([
            'branch_id' => $request->branch_id,
            'duplicate_punch_threshold_minutes' => $request->duplicate_punch_threshold_minutes,
        ]);

        // Load the branch relationship
        $setting->load('branch');

        return ResponseWithSuccessData($lang, $setting, 1);
    }

    /**
     * Delete HR setting for a specific branch
     */
    public function destroy(Request $request, $branchId)
    {
        $lang = $request->header('lang', 'ar');

        $branch = Branch::find($branchId);

        if (!$branch) {
            $message = $lang == 'en' ? 'Branch not found' : 'الفرع غير موجود';
            return respondError($message, 404);
        }

        $setting = HrSetting::where('branch_id', $branchId)->first();

        if (!$setting) {
            $message = $lang == 'en' ? 'HR setting not found for this branch' : 'لم يتم العثور على إعدادات الموارد البشرية لهذا الفرع';
            return respondError($message, 404);
        }

        $setting->delete();

        return RespondWithSuccessRequest($lang, 1);
    }
}
