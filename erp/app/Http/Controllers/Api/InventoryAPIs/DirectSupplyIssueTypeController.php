<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Resources\Inventory\DirectSupplyIssueTypeResource;
use Illuminate\Http\Request;
use App\Models\DirectSupplyPermissionStatusSetting;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Models\DirectSupplyIssueType;
use Illuminate\Support\Facades\Validator;

use App\Services\Inventory_Services\DirectSupplyPermissionStatusSettingService;
use Illuminate\Validation\Rule;

class DirectSupplyIssueTypeController extends Controller
{
    protected $hidden = [
        //  'title_ar',
        // 'title_en',
        // 'description_ar',
        // 'description_en',
        'created_at',
        'updated_at',
        'deleted_at',
        'deleted_by',
        'updated_by',
        'created_by'
    ];
    protected $fillable = [

        'status',
    ];
   public function index(Request $request)
{
    $lang = $request->header('lang', 'en');

    try {
        $dataQuery = DirectSupplyIssueType::query();
        $result = paginateOrGetAll($dataQuery, $request, $this->hidden);

       $data = DirectSupplyIssueTypeResource::collection($result['data']);

        return ResponseWithSuccessDataPaginated($lang, [
            'data' => $data,
            'meta' => $result['meta']
        ], 1);

    } catch (\Exception $e) {
        return RespondWithBadRequestData($lang, 2);
    }
}


    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        try {
            $result = DirectSupplyIssueType::find($id)?->makeHidden($this->hidden);
            if (!$result) {

                return respondError(__('validation.not_found'), 404);
            }
            $data = [
                'id' => $result->id,
                'name_ar' => $result->title_ar,
                'name_en' => $result->title_en,
                'name' => $result->name,

                'description_ar' => $result->description_ar,
                'description_en' => $result->description_en,
                'status' => (bool)  $result->status

            ];
            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            $validator = Validator::make($request->all(), [
                'name_en' => 'required|unique:direct_supply_issue_types,title_en',
                'name_ar' => 'required|unique:direct_supply_issue_types,title_ar',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'status' => 'nullable|in:0,1',
            ]);
            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }
            $issue = new DirectSupplyIssueType();
            $issue->title_en = $request->name_en;
            $issue->title_ar = $request->name_ar;
            $issue->description_ar = $request->description_ar;
            $issue->description_en = $request->description_en;
            $issue->status = $request->status ?? 1;
            $issue->save();
            $data = [
                'id' => $issue->id,
                'name_ar' => $issue->title_ar,
                'name_en' => $issue->title_en,
                'name' => $issue->name,

                'description_ar' => $issue->description_ar,
                'description_en' => $issue->description_en,
                'status' => (bool)  $issue->status

            ];
            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => false,
                'message' => 'Internal server error',
                'data' => null,
                'errorData' => ['error' => $e->getMessage()],
                'validation_type' => false
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $validator = Validator::make($request->all(), [
                'name_en' => [
                    'required',
                    Rule::unique('direct_supply_issue_types', 'title_en')->ignore($id),
                ],
                'name_ar' => [
                    'required',
                    Rule::unique('direct_supply_issue_types', 'title_ar')->ignore($id),
                ],

                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'status' => 'nullable|in:0,1',
            ]);
            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }
            $issue = DirectSupplyIssueType::findOrFail($id);
            $issue->title_en = $request->name_en;
            $issue->title_ar = $request->name_ar;
            $issue->description_ar = $request->description_ar;
            $issue->description_en = $request->description_en;
            $issue->status = $request->status ?? $issue->status;
            $issue->save();

            $data = [
                'id' => $issue->id,
                'name_ar' => $issue->title_ar,
                'name_en' => $issue->title_en,
                'name' => $issue->name,

                'description_ar' => $issue->description_ar,
                'description_en' => $issue->description_en,
                'status' => (bool)  $issue->status

            ];
            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => false,
                'message' => 'Internal server error',
                'data' => null,
                'errorData' => ['error' => $e->getMessage()],
                'validation_type' => false
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $exists = DirectSupplyIssueType::findOrFail($id);
            App::setLocale($lang);

            if (!$exists) {
                return respondError(__('validation.not_found'), 404);
            }
            if (in_array($id, [1, 2, 3])) {
                return respondError(__('validation.cannot_delete_default'), 400);
            }

            //handle relation for if it used in any dsp cannot delete
            // if($exists->directSupplyIssues()->count() > 0){
            //     return respondError(__('direct_supply_issue_type.cannot_delete'), 400);
            // }
            $exists->deleted_by = getAuthenticatedUser()->id;
            $exists->save();
            $exists->delete();
            return ResponseWithSuccessData($lang, null, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
