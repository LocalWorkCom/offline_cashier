<?php


namespace App\Services\Inventory_Services;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\DirectSupplyPermissionStatusSetting;
use Carbon\Carbon;

class DirectSupplyPermissionStatusSettingService
{

    public function index()
    {

        $directSupplyPermissionStatusSetting = DirectSupplyPermissionStatusSetting::orderBy('position')->orderBy('id');

        return $directSupplyPermissionStatusSetting;
    }

    public function reorder(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach ($request->order as $item) {
                DirectSupplyPermissionStatusSetting::where('id', $item['id'])
                    ->update(['position' => $item['position']]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'code' => 500,
                'status' => false,
                'message' => 'Failed to reorder statuses',
                'data' => null,
                'errorData' => ['error' => $e->getMessage()],
                'validation_type' => false
            ];
        }
    }


    public function store(Request $request, $checkToken)
    {
        $lang = $request->header('lang', 'ar');

        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                Rule::unique('direct_supply_permission_status_settings')->whereNull('deleted_at')
            ],
            'name_en' => [
                'nullable',
                'string',
                Rule::unique('direct_supply_permission_status_settings')->whereNull('deleted_at')
            ],
            'description_ar' => [
                'nullable',
                'string',
            ],
            'description_en' => [
                'nullable',
                'string',
            ],
            'position' => [
                'nullable',
                'integer'
            ],
            'type' => [
                'required',
                Rule::in(['start', 'intermediate', 'final']),
            ],
            'previous_statuses' => [
                'nullable',
                'array'
            ],
            'previous_statuses.*' => ['integer'],
            'next_statuses' => [
                'nullable',
                'array'
            ],
            'next_statuses.*' => ['integer'],
            'behavior' => [
                'required',
                Rule::in(['manual', 'automatic']),
            ],
            'active' => ['required', 'boolean' ],
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

        $status = new DirectSupplyPermissionStatusSetting();
        $status->name_ar = $request->name_ar;
        $status->name_en = $request->name_en;
        $status->description_ar = $request->description_ar ?? null;
        $status->description_en = $request->description_en ?? null;
        $status->position = $request->position ?? 0;
        $status->type = $request->type;
        $status->previous_statuses = $request->previous_statuses ?? null;
        $status->next_statuses = $request->next_statuses ?? null;
        $status->behavior = $request->behavior;
        $status->active = $request->active;
        $status->created_at = Carbon::now();
        $status->modified_at = Carbon::now();
        $status->created_by = authActionSave()['by'];
        $status->created_by_type = authActionSave()['type'];

        $status->save();

        // Return the created status object instead of true
        return $status;
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
        $status = DirectSupplyPermissionStatusSetting::find($id);
        if (!$status) {
            // Return array for error instead of response
            return [
                'code' => 404,
                'status' => false,
                'message' => 'directSupplyPermissionStatusSetting not found',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }

        $validator = Validator::make($request->all(), [

            'name_ar' => [
                'required',
                'string',
                Rule::unique('direct_supply_permission_status_settings')->whereNull('deleted_at')->ignore($id)
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('direct_supply_permission_status_settings')->whereNull('deleted_at')->ignore($id)
            ],
            'description_ar' => [
                'nullable',
                'string',
            ],
            'description_en' => [
                'nullable',
                'string',
            ],
            'position' => [
                'nullable',
                'integer'
            ],
            'type' => [
                'required',
                Rule::in(['start', 'intermediate', 'final']),
            ],
            'previous_statuses' => [
                'nullable',
                'array'
            ],
            'previous_statuses.*' => ['integer'],
            'next_statuses' => [
                'nullable',
                'array'
            ],
            'next_statuses.*' => ['integer'],
            'behavior' => [
                'required',
                Rule::in(['manual', 'automatic']),
            ],
            'active' => ['required', 'boolean']

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

        $status = DirectSupplyPermissionStatusSetting::find($id);
        // Check if names changed
        // if ($status->name_ar == $request->name_ar && $status->name_en == $request->name_en) {
        //     return [
        //         'code' => 400,
        //         'status' => false,
        //         'message' => 'directSupplyPermissionStatusSetting not change',
        //         'data' => null,
        //         'errorData' => $validator->errors(),
        //         'validation_type' => true
        //     ];
        // }

        // Check uniqueness but exclude the current record
        $exists_ar = DirectSupplyPermissionStatusSetting::where('id', '!=', $id)->where('name_ar', $request->name_ar)->exists();
        $exists_en = DirectSupplyPermissionStatusSetting::where('id', '!=', $id)->where('name_en', $request->name_en)->exists();

        if ($exists_ar || $exists_en) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'DirectSupplyPermissionStatusSetting not change',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        $status->name_ar = $request->name_ar;
        $status->name_en = $request->name_en;
        $status->description_ar = $request->description_ar ?? null;
        $status->description_en = $request->description_en ?? null;
        $status->position = $request->position ?? 0;
        $status->type = $request->type;
        $status->previous_statuses = $request->previous_statuses ?? null;
        $status->next_statuses = $request->next_statuses ?? null;
        $status->behavior = $request->behavior;
        $status->active = $request->active;
        $status->created_at = Carbon::now();
        $status->modified_at = Carbon::now();
        $status->modified_by = authActionSave()['by'];
        $status->modified_by_type = authActionSave()['type'];
        $status->save();

        return $status;
    }

    public function delete(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $status = DirectSupplyPermissionStatusSetting::find($id);
        if (!$status) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        // // 🔹 Check if used in related tables before deletion
        // $usedInPermissions = DB::table('direct_supply_permissions')
        //     ->where('status_id', $id)
        //     ->exists();

        // $usedInRequests = DB::table('direct_supply_requests')
        //     ->where('permission_status_id', $id)
        //     ->exists();

        // if ($usedInPermissions || $usedInRequests) {
        //     return respondError(__('branch_menu_category.cannot_delete_in_use'), 400);
        // }

        // Soft delete metadata
        $status->deleted_by = authActionSave()['by'];
        $status->deleted_by_type = authActionSave()['type'];
        $status->save();

        // Perform soft delete
        $status->delete();

        return RespondWithSuccessRequest($lang, 1);
    }
}
