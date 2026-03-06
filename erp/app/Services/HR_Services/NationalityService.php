<?php


namespace App\Services\HR_Services;

use App\Models\Nationality;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NationalityService
{

    public function index($checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        $nationalities = Nationality::withCount('employees')
            ->with('employees');
        return $nationalities;
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
                Rule::unique('nationalities')->whereNull('deleted_at')
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('nationalities')->whereNull('deleted_at')
            ],
            // 'logo' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

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

        $nationality = new Nationality();
        $nationality->name_ar = $request->name_ar;
        $nationality->name_en = $request->name_en;
        $nationality->created_by = authActionSave()['by'];
        $nationality->created_by_type = authActionSave()['type'];

        $nationality->save();

        // Return the created nationality object instead of true
        return $nationality;
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
        $Nationality = Nationality::find($id);
        if (!$Nationality) {
            // Return array for error instead of response
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Nationality not found',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }

        $validator = Validator::make($request->all(), [

            'name_ar' => [
                'required',
                'string',
                Rule::unique('nationalities')->whereNull('deleted_at')->ignore($id)
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('nationalities')->whereNull('deleted_at')->ignore($id)
            ],

            // Apply validation only if a logo is provided in the request
            // 'logo' => $request->hasFile('logo') ? 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048' : 'nullable',
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

        $Nationality = Nationality::find($id);
        // Check if names changed
        if ($Nationality->name_ar == $request->name_ar && $Nationality->name_en == $request->name_en) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Nationality not change',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        // Check uniqueness but exclude the current record
        $exists_ar = Nationality::where('id', '!=', $id)->where('name_ar', $request->name_ar)->exists();
        $exists_en = Nationality::where('id', '!=', $id)->where('name_en', $request->name_en)->exists();

        if ($exists_ar || $exists_en) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Nationality not change',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        // $modify_by = Auth::guard('admin')->user()->id;

        $Nationality->name_ar = $request->name_ar;
        $Nationality->name_en = $request->name_en;
        $Nationality->modified_by = authActionSave()['by'];
        $Nationality->modified_by_type = authActionSave()['type'];
        // Only update the logo if a new one is uploaded
        // if ($request->hasFile('logo')) {
        //     // Delete the old logo
        //     DeleteFile('images/bank_names', $BankName->logo);

        //     // Upload the new logo
        //     $logo = $request->file('logo');
        //     UploadFile('images/bank_names', 'logo', $BankName, $logo);
        // }

        $Nationality->save();

        return $Nationality;
    }

    public function delete(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        // Find the nationality by ID, or throw a 404 if not found
        $nationality = Nationality::find($id);
        if (!$nationality) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        // DeleteFile('images/nationality', $nationality->logo);

        $nationality->deleted_by = authActionSave()['by'];
        $nationality->deleted_by_type = authActionSave()['type'];
        // Delete the nationality
        $nationality->delete();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
}
