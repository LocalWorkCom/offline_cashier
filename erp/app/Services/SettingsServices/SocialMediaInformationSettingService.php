<?php

namespace App\Services\SettingsServices;

use App\Models\SocialMediaInformationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SocialMediaInformationSettingService
{

    public function index(Request $request, $checkToken)
    {
        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        $socialMediaInformationSetting = SocialMediaInformationSetting::query()->with('companyProfileSetting');

        return $socialMediaInformationSetting;
    }

    public function store(Request $request, $checkToken)
    {
        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            // Return array instead of response to avoid nesting
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Token validation failed',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }
        $validator = Validator::make($request->all(), [
            'android_app_link' => 'required|url',
            'ios_app_link' => 'required|url',
            'facebook' => 'required|url',
            'instagram' => 'required|url',
            'snapchat' => 'required|url',
            'twitter' => 'required|url',
            'tiktok' => 'required|url',
            'company_id' => 'required|exists:company_profile_settings,id',
        ]);

        if ($validator->fails()) {
            // Return array instead of response
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }
        $exists = SocialMediaInformationSetting::where('company_id', $request->company_id)->exists();

        if ($exists) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Social media information already exists for this company.',
                'data' => null,
                'errorData' => ['company_id' => ['This company already has social media information.']],
                'validation_type' => true
            ];
        }
        try {
            $socialMediaInformationSetting = new SocialMediaInformationSetting($request->all());
            // $socialMediaInformationSetting->created_by = Auth::id();
            $socialMediaInformationSetting->created_by = authActionSave()['by'];
            $socialMediaInformationSetting->created_by_type = authActionSave()['type'];
            $socialMediaInformationSetting->save();

            $socialMediaInformationSetting->refresh();

            // Return the businessActivity object directly
            return $socialMediaInformationSetting;
        } catch (\Exception $e) {
            // Return error array instead of throwing exception
            return [
                'code' => 500,
                'status' => false,
                'message' => 'Failed to create socialMediaInformationSetting',
                'data' => null,
                'errorData' => ['error' => $e->getMessage()],
                'validation_type' => false
            ];
        }
    }

    public function update(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            // Return array for error instead of response
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Token validation failed',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }

        $socialMediaInformationSetting = SocialMediaInformationSetting::find($id);
        if (!$socialMediaInformationSetting) {
            // Return array for error instead of response
            return [
                'code' => 404,
                'status' => false,
                'message' => 'socialMediaInformationSetting not found',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'android_app_link' => 'required|url',
            'ios_app_link' => 'required|url',
            'facebook' => 'required|url',
            'instagram' => 'required|url',
            'snapchat' => 'required|url',
            'twitter' => 'required|url',
            'tiktok' => 'required|url',
            'company_id' => 'required|exists:company_profile_settings,id',
        ]);
        if ($validator->fails()) {
            // Return array for validation errors instead of response
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }
        try {
            // Update contact information
            // $socialMediaInformationSetting->update($request->all());
            $socialMediaInformationSetting->update($request->all());

            // Check if anything changed
            // if ($socialMediaInformationSetting->isClean()) {
            //     return [
            //         'code' => 400,
            //         'status' => false,
            //         'message' => 'No changes detected for this company.',
            //         'data' => null,
            //         'errorData' => null,
            //         'validation_type' => true
            //     ];
            // }
            $socialMediaInformationSetting->modified_by = authActionSave()['by'];
            $socialMediaInformationSetting->modified_by_type = authActionSave()['type'];
            $socialMediaInformationSetting->save();
            $socialMediaInformationSetting->refresh();

            // Return the socialMediaInformationSetting object directly
            return $socialMediaInformationSetting;
        } catch (\Exception $e) {
            // Return error array instead of throwing exception
            return [
                'code' => 500,
                'status' => false,
                'message' => 'Failed to update socialMediaInformationSetting',
                'data' => null,
                'errorData' => ['error' => $e->getMessage()],
                'validation_type' => false
            ];
        }
    }

    public function destroy(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $socialMediaInformationSetting = SocialMediaInformationSetting::find($id);
        if (!$socialMediaInformationSetting) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        $socialMediaInformationSetting->update(['deleted_by' => auth('admin')->id()]);
        $socialMediaInformationSetting->delete();

        return RespondWithSuccessRequest($lang, 1);
    }

    public function restore(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $socialMediaInformationSetting = SocialMediaInformationSetting::withTrashed()->findOrFail($id);
            $socialMediaInformationSetting->restore();
            return ResponseWithSuccessData($lang, $socialMediaInformationSetting, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring SocialMediaInformationSetting: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
