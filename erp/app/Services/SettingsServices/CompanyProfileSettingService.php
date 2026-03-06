<?php

namespace App\Services\SettingsServices;

use App\Models\CompanyProfileSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CompanyProfileSettingService
{
    public function index(Request $request, $checkToken)
    {
        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        $companyProfileSettings = CompanyProfileSetting::query()->with('businessActivity');

        return $companyProfileSettings;
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
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('company_profile_settings')->whereNull('deleted_at')
            ],
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('company_profile_settings')->whereNull('deleted_at')
            ],
            'description_ar' => 'required|string',
            'description_en' => 'required|string',
            'business_activity' => 'required|exists:business_activities,id',
            // 'trade_license' => 'required|string|max:255',
            'license_expiry_date' => 'required|date',
            // 'tax_registration_number' => 'required|string|max:255',
            'trade_license' => [
                'required',
                'string',
                Rule::unique('company_profile_settings')->whereNull('deleted_at')
            ],
            'tax_registration_number' => [
                'required',
                'string',
                Rule::unique('company_profile_settings')->whereNull('deleted_at')
            ],
            'capital' => 'required|string|max:255',
            'scanned_trade_license' => 'nullable|image|mimes:pdf,jpg,png|max:5000',
            'logo' => 'nullable|image|mimes:jpg,png,jpeg|max:5000',
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

            $companyProfileSettings = new CompanyProfileSetting($request->all());
            // $companyProfileSettings->created_by = Auth::guard('admin')->id();
            $companyProfileSettings->created_by = authActionSave()['by'];
            $companyProfileSettings->created_by_type = authActionSave()['type'];
            $companyProfileSettings->save();

            if ($request->hasFile('logo')) {
                UploadFile('images/companyProfileSettings', 'logo', $companyProfileSettings, $request->file('logo'));
            }
            if ($request->hasFile('scanned_trade_license')) {
                UploadFile('documents/trade_licenses', 'scanned_trade_license', $companyProfileSettings, $request->file('scanned_trade_license'));
            }

            $companyProfileSettings->refresh();

            // Return the businessActivity object directly
            return $companyProfileSettings;
        } catch (\Exception $e) {
            // Return error array instead of throwing exception
            return [
                'code' => 500,
                'status' => false,
                'message' => 'Failed to create business activity',
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

        $companyProfileSettings = CompanyProfileSetting::find($id);
        if (!$companyProfileSettings) {
            // Return array for error instead of response
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Company Profile not found',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }
        if ($request->hasFile('logo')) {

            $validator = Validator::make($request->all(), [
                'name_ar' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('company_profile_settings')->whereNull('deleted_at')->ignore($id)
                ],
                'name_en' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('company_profile_settings')->whereNull('deleted_at')->ignore($id)
                ],
                'description_ar' => 'required|string',
                'description_en' => 'required|string',
                'business_activity' => 'required|exists:business_activities,id',
                'license_expiry_date' => 'required|date',
                'trade_license' => [
                    'required',
                    'string',
                    Rule::unique('company_profile_settings')->whereNull('deleted_at')->ignore($id)
                ],
                'tax_registration_number' => [
                    'required',
                    'string',
                    Rule::unique('company_profile_settings')->whereNull('deleted_at')->ignore($id)
                ],
                'capital' => 'required|string|max:255',
                'logo' => 'nullable|image|mimes:jpg,png,jpeg|max:5000',
            ]);
        } elseif ($request->hasFile('scanned_trade_license')) {
            $validator = Validator::make($request->all(), [
                'name_ar' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('company_profile_settings')->whereNull('deleted_at')->ignore($id)
                ],
                'name_en' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('company_profile_settings')->whereNull('deleted_at')->ignore($id)
                ],
                'description_ar' => 'required|string',
                'description_en' => 'required|string',
                'business_activity' => 'required|exists:business_activities,id',
                'license_expiry_date' => 'required|date',
                'trade_license' => [
                    'required',
                    'string',
                    Rule::unique('company_profile_settings')->whereNull('deleted_at')->ignore($id)
                ],
                'tax_registration_number' => [
                    'required',
                    'string',
                    Rule::unique('company_profile_settings')->whereNull('deleted_at')->ignore($id)
                ],
                'capital' => 'required|string|max:255',
                'scanned_trade_license' => 'nullable|image|mimes:jpg,png,jpeg|max:5000',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'name_ar' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('company_profile_settings')->whereNull('deleted_at')->ignore($id)
                ],
                'name_en' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('company_profile_settings')->whereNull('deleted_at')->ignore($id)
                ],
                'description_ar' => 'required|string',
                'description_en' => 'required|string',
                'business_activity' => 'required|exists:business_activities,id',
                'license_expiry_date' => 'required|date',
                'trade_license' => [
                    'required',
                    'string',
                    Rule::unique('company_profile_settings')->whereNull('deleted_at')->ignore($id)
                ],
                'tax_registration_number' => [
                    'required',
                    'string',
                    Rule::unique('company_profile_settings')->whereNull('deleted_at')->ignore($id)
                ],
                'capital' => 'required|string|max:255',
            ]);
        }

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

            $companyProfileSettings->update($request->all());
            // $companyProfileSettings->modified_by = Auth::guard('admin')->id();
            $companyProfileSettings->modified_by = authActionSave()['by'];
            $companyProfileSettings->modified_by_type = authActionSave()['type'];
            $companyProfileSettings->save();

            if ($request->hasFile('logo')) {
                DeleteFile('images/companyProfileSettings', $companyProfileSettings->logo);
                UploadFile('images/companyProfileSettings', 'logo', $companyProfileSettings, $request->file('logo'));
            }
            if ($request->hasFile('scanned_trade_license')) {
                DeleteFile('documents/trade_licenses', $companyProfileSettings->scanned_trade_license);
                UploadFile('documents/trade_licenses', 'scanned_trade_license', $companyProfileSettings, $request->file('scanned_trade_license'));
            }
            $companyProfileSettings->refresh();

            // Return the companyProfileSettings object directly
            return $companyProfileSettings;
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

        $companyProfileSettings = CompanyProfileSetting::find($id);
        if (!$companyProfileSettings) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        if ($companyProfileSettings->logo) {
            DeleteFile('images/companyProfileSettings', $companyProfileSettings->logo);
        }
        if ($companyProfileSettings->scanned_trade_license) {
            DeleteFile('documents/trade_licenses', $companyProfileSettings->scanned_trade_license);
        }
        // $companyProfileSettings->update(['deleted_by' => auth('admin')->id()]);

        $companyProfileSettings->deleted_by = authActionSave()['by'];
        $companyProfileSettings->deleted_by_type = authActionSave()['type'];
        $companyProfileSettings->delete();
        return RespondWithSuccessRequest($lang, 1);
    }

    public function restore(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $companyProfileSettings = CompanyProfileSetting::withTrashed()->findOrFail($id);
            $companyProfileSettings->restore();
            return ResponseWithSuccessData($lang, $companyProfileSettings, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring CompanyProfileSetting: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
