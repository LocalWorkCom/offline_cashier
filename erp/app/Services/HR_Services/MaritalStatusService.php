<?php


namespace App\Services\HR_Services;

use App\Models\MaritalStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MaritalStatusService
{

    public function index()
    {
        $maritalStatuses = MaritalStatus::withCount('employees')->with('employees');
        return $maritalStatuses;
    }

    public function store(Request $request, $checkToken)
    {
        $lang = app()->getLocale();
        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                Rule::unique('marital_statuses', 'name_ar')->whereNull('deleted_at')
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('marital_statuses', 'name_en')->whereNull('deleted_at')
            ],
        ], [
            'name_ar.required' => __('validation.required', ['attributes' => 'Arabic name'], $lang),
            'name_ar.unique' => __('validation.unique', ['attributes' => 'Arabic name'], $lang),
            'name_en.required' => __('validation.required', ['attributes' => 'English name'], $lang),
            'name_en.unique' => __('validation.unique', ['attributes' => 'English name'], $lang),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $created_by = auth('employee')->user()->id ?? auth('admin')->user()->id;

        $MaritalStatus = new MaritalStatus();
        $MaritalStatus->name_ar = $request->name_ar;
        $MaritalStatus->name_en = $request->name_en;
        $MaritalStatus->created_by = $created_by;

        $MaritalStatus->save();

        return $MaritalStatus;
    }

    public function update(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|unique:marital_statuses,name_ar,' . $id,
            'name_en' => 'required|string|unique:marital_statuses,name_en,' . $id,
        ], [
            'name_ar.required' => __('validation.required', ['attributes' => 'Arabic name'], $lang),
            'name_ar.unique' => __('validation.unique', ['attributes' => 'Arabic name'], $lang),
            'name_en.required' => __('validation.required', ['attributes' => 'English name'], $lang),
            'name_en.unique' => __('validation.unique', ['attributes' => 'English name'], $lang),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $MaritalStatus = MaritalStatus::find($id);

        $modified_by = auth('employee')->user()->id ?? auth('admin')->user()->id;

        $MaritalStatus->name_ar = $request->name_ar;
        $MaritalStatus->name_en = $request->name_en;
        $MaritalStatus->modified_by = $modified_by;

        $MaritalStatus->save();

        return $MaritalStatus;
    }


    public function delete($id)
    {
        $lang = app()->getLocale();
        $MaritalStatus = MaritalStatus::find($id);
        if (!$MaritalStatus) {
            return  RespondWithBadRequestData($lang, 8);
        }
        $MaritalStatus->delete();

        return $MaritalStatus;
    }
}
