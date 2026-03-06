<?php

namespace App\Services\SettingsServices;

use App\Models\BusinessActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BusinessActivityService
{
    public function index(Request $request, $checkToken)
    {
        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        $businessActivity = BusinessActivity::query();


        return $businessActivity;
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
                Rule::unique('business_activities')->whereNull('deleted_at')
            ],
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('business_activities')->whereNull('deleted_at')
            ],
            'description_ar' => 'required|string',
            'description_en' => 'required|string',
            'is_active' => 'required|boolean',
            'logo' => 'required|image|mimes:jpg,png,jpeg|max:5000',
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
            $businessActivity = new BusinessActivity($request->all());
            $businessActivity->created_by = authActionSave()['by'];
            $businessActivity->created_by_type = authActionSave()['type'];
            $businessActivity->save();

            if ($request->hasFile('logo')) {
                UploadFile('images/business_activity', 'logo', $businessActivity, $request->file('logo'));
            }

            $businessActivity->refresh();

            // Return the businessActivity object directly
            return $businessActivity;
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

        $businessActivity = BusinessActivity::find($id);
        if (!$businessActivity) {
            // Return array for error instead of response
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Business activity not found',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }

        // Fix the unique validation to ignore current record
        $validationRules = [
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('business_activities')->whereNull('deleted_at')->ignore($id)
            ],
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('business_activities')->whereNull('deleted_at')->ignore($id)
            ],
            'description_ar' => 'required|string',
            'description_en' => 'required|string',
            'is_active' => 'required|boolean',
        ];

        if ($request->hasFile('logo')) {
            $validationRules['logo'] = 'required|image|mimes:jpg,png,jpeg|max:5000';
        }

        $validator = Validator::make($request->all(), $validationRules);

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
            $businessActivity->update($request->all());
            $businessActivity->modified_by = authActionSave()['by'];
            $businessActivity->modified_by_type = authActionSave()['type'];
            $businessActivity->save();

            if ($request->hasFile('logo')) {
                DeleteFile('images/business_activity', $businessActivity->logo);
                UploadFile('images/business_activity', 'logo', $businessActivity, $request->file('logo'));
            }

            // Refresh to get updated data
            $businessActivity->refresh();

            // Return the businessActivity object directly
            return $businessActivity;
        } catch (\Exception $e) {
            // Return error array instead of throwing exception
            return [
                'code' => 500,
                'status' => false,
                'message' => 'Failed to update business activity',
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

        $BusinessActivity = BusinessActivity::find($id);
        if (!$BusinessActivity) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        if ($BusinessActivity->logo) {
            DeleteFile('images/business_activity', $BusinessActivity->logo);
        }
        // $BusinessActivity->update(['deleted_by' => auth('admin')->id()]);

        $BusinessActivity->deleted_by = authActionSave()['by'];
        $BusinessActivity->deleted_by_type = authActionSave()['type'];
        $BusinessActivity->delete();
        return RespondWithSuccessRequest($lang, 1);
    }
}
