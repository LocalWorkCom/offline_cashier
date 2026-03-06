<?php


namespace App\Services\SettingsServices;

use App\Models\BankName;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BankNameService
{
  public function index(Request $request, $checkToken)
{
    $lang = app()->getLocale();

    if (!CheckToken() && $checkToken) {
        return RespondWithBadRequest($lang, 5);
    }

    // Return query builder instead of collection
    $query = BankName::query()
        ->addSelect([
            'employees_count' => DB::table('employee_banking_info')
                ->selectRaw('COUNT(DISTINCT employee_id)')
                ->whereColumn('bank_name_id', 'bank_names.id')
        ]);

    // Eager load the relationships
    $query->with(['employeeBankingInfo.employee']);

    return $query;
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
                Rule::unique('bank_names')->whereNull('deleted_at')
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('bank_names')->whereNull('deleted_at')
            ],
            'logo' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

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

        $name_ar = $request->name_ar;
        $name_en = $request->name_en;

        // Check if either name already exists
        if (CheckExistColumnValue('bank_names', 'name_ar', $name_ar) || CheckExistColumnValue('bank_names', 'name_en', $name_en)) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        // $created_by = Auth::guard('admin')->user()->id;

        $BankName = new BankName();
        $BankName->name_ar = $name_ar;
        $BankName->name_en = $name_en;
        $BankName->created_by = authActionSave()['by'];
        $BankName->created_by_type = authActionSave()['type'];
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            UploadFile('images/bank_names', 'logo', $BankName, $logo);
        }

        $BankName->save();

        return $BankName;
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
        $bank_names = BankName::find($id);
        if (!$bank_names) {
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
        // Validate name fields
        $validator = Validator::make($request->all(), [

            'name_ar' => [
                'required',
                'string',
                Rule::unique('bank_names')->whereNull('deleted_at')->ignore($id)
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('bank_names')->whereNull('deleted_at')->ignore($id)
            ],

            // Apply validation only if a logo is provided in the request
            'logo' => $request->hasFile('logo') ? 'nullable|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048' : 'nullable',
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

        $BankName = BankName::find($id);
        // Check if names changed
        if ($BankName->name_ar == $request->name_ar && $BankName->name_en == $request->name_en && !$request->hasFile('logo')) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        // Check uniqueness but exclude the current record
        $exists_ar = BankName::where('id', '!=', $id)->where('name_ar', $request->name_ar)->exists();
        $exists_en = BankName::where('id', '!=', $id)->where('name_en', $request->name_en)->exists();

        if ($exists_ar || $exists_en) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        // $modify_by = Auth::guard('admin')->user()->id;

        $BankName->name_ar = $request->name_ar;
        $BankName->name_en = $request->name_en;
        $BankName->modified_by = authActionSave()['by'];
        $BankName->modified_by_type = authActionSave()['type'];
        // Only update the logo if a new one is uploaded
        if ($request->hasFile('logo')) {
            // Delete the old logo
            DeleteFile('images/bank_names', $BankName->logo);

            // Upload the new logo
            $logo = $request->file('logo');
            UploadFile('images/bank_names', 'logo', $BankName, $logo);
        }

        $BankName->save();

        return $BankName;
    }


    public function delete(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        // Find the BankName by ID, or throw a 404 if not found
        $BankName = BankName::find($id);
        if (!$BankName) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        DeleteFile('images/bank_names', $BankName->logo);

        $BankName->deleted_by = authActionSave()['by'];
        $BankName->deleted_by_type = authActionSave()['type'];
        // Delete the BankName
        $BankName->delete();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
}
