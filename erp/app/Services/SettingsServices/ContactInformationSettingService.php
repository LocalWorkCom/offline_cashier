<?php

namespace App\Services\SettingsServices;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\ContactInformationSetting;
use Illuminate\Support\Facades\Validator;

class ContactInformationSettingService
{

    public function index(Request $request, $checkToken)
    {
        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        $contactInformationSetting = ContactInformationSetting::query()->with('companyProfileSetting');

        return $contactInformationSetting;
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
            'company_address_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('contact_information_settings')->whereNull('deleted_at')
            ],
            'company_address_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('contact_information_settings')->whereNull('deleted_at')
            ],
            'map_link' => 'nullable|string|max:255',
            'phone_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('contact_information_settings')->whereNull('deleted_at'),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('contact_information_settings')->whereNull('deleted_at'),
            ],
            'website_link' => 'nullable|string|max:255',
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
        try {
            $contactInformationSetting = new ContactInformationSetting($request->all());
            $contactInformationSetting->created_by = authActionSave()['by'];
            $contactInformationSetting->created_by_type = authActionSave()['type'];
            $contactInformationSetting->save();

            $contactInformationSetting->refresh();

            // Return the businessActivity object directly
            return $contactInformationSetting;
        } catch (\Exception $e) {
            // Return error array instead of throwing exception
            return [
                'code' => 500,
                'status' => false,
                'message' => 'Failed to create contactInformationSetting',
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
        $contactInformationSetting = ContactInformationSetting::find($id);
        if (!$contactInformationSetting) {
            // Return array for error instead of response
            return [
                'code' => 404,
                'status' => false,
                'message' => 'contactInformationSetting not found',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }

        $validator = Validator::make($request->all(), [
            'company_address_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('contact_information_settings')->whereNull('deleted_at')->ignore($id)
            ],
            'company_address_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('contact_information_settings')->whereNull('deleted_at')->ignore($id)
            ],
            'map_link' => 'nullable|string|max:255',
            'phone_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('contact_information_settings')->ignore($id)->whereNull('deleted_at'),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('contact_information_settings')->ignore($id)->whereNull('deleted_at'),
            ],
            'website_link' => 'nullable|string|max:255',
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
            $contactInformationSetting->update($request->all());
            $contactInformationSetting->modified_by = authActionSave()['by'];
            $contactInformationSetting->modified_by_type = authActionSave()['type'];
            $contactInformationSetting->save();
            $contactInformationSetting->refresh();

            // Return the contactInformationSetting object directly
            return $contactInformationSetting;
        } catch (\Exception $e) {
            // Return error array instead of throwing exception
            return [
                'code' => 500,
                'status' => false,
                'message' => 'Failed to update companyProfileSettings',
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

        $contactInformationSetting = ContactInformationSetting::find($id);
        if (!$contactInformationSetting) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        $contactInformationSetting->deleted_by = authActionSave()['by'];
        $contactInformationSetting->deleted_by_type = authActionSave()['type'];
        // $contactInformationSetting->update(['deleted_by' => auth('admin')->id()]);
        $contactInformationSetting->delete();
        return RespondWithSuccessRequest($lang, 1);
    }

    public function restore(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $contactInformationSetting = ContactInformationSetting::withTrashed()->findOrFail($id);
            $contactInformationSetting->restore();
            return ResponseWithSuccessData($lang, $contactInformationSetting, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring ContactInformationSetting: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
