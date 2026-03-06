<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Services\HR_Services\JobRelatedPenaltyService;
use Illuminate\Http\Request;

class JobRelatedPenaltyController extends Controller
{
    protected $JobRelatedPenaltyService;

    public function __construct(JobRelatedPenaltyService $JobRelatedPenaltyService)
    {
        $this->JobRelatedPenaltyService = $JobRelatedPenaltyService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            $data = $this->JobRelatedPenaltyService->index($request);
            // $visible = ['name', 'reason'];
            // $fields = ['name_ar', 'name_en', 'name_site', 'reason_ar', 'reason_en'];
            $response = paginateOrGetAll($data, $request, null, null);
            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function report(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            $data = $this->JobRelatedPenaltyService->report($request);
            // $visible = ['name', 'reason'];
            // $fields = ['name_ar', 'name_en', 'name_site', 'reason_ar', 'reason_en'];
            $response = paginateOrGetAll($data, $request, null, null);
            // dd($response);
            $responseData['data'] = $response['data']->map(function ($res) {
                return [
                    'id' => $res->id,
                    'employee_name' => $res->employee->first_name . ' ' . $res->employee->last_name,
                    'employee_id' => $res->employee_id,
                    'type' => $res->type,
                    'status' => $res->status,
                    'curr_possition_id' => $res->curr_possition_id,
                    'new_possition_id' => $res->new_possition_id,
                    'curr_department_id' => $res->curr_department_id,
                    // 'curr_department_name' => $res->currDepartment->name_ar,
                    'new_department_id' => $res->new_department_id,
                    'curr_branch_id' => $res->curr_branch_id,
                    'new_branch_id' => $res->new_branch_id,
                    'effective_date' => $res->effective_date,
                    'reason' => $res->reason,
                    'restricted_system' => $res->restricted_system,
                ];
            })->toArray();
            $responseData['meta'] = $response['meta'];
            return ResponseWithSuccessDataPaginated($lang, $responseData, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function show(Request $request, $id)
    {
        $lang =  $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            return $response = $this->JobRelatedPenaltyService->show($request, $id);
        } catch (\Exception $e) {
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }
    public function add(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            return $response = $this->JobRelatedPenaltyService->add($request);
        } catch (\Exception $e) {
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }
    public function changeStatus(Request $request, $id)
    {
        $lang =  $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            return $response = $this->JobRelatedPenaltyService->changeStatus($request, $id);
        } catch (\Exception $e) {
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function update(Request $request, $id)
    {
        $lang =  $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            return $response = $this->JobRelatedPenaltyService->update($request, $id);
        } catch (\Exception $e) {
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $request['lang'] = $lang;
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            return $response = $this->JobRelatedPenaltyService->destroy($request, $id);
        } catch (\Exception $e) {
            // return RespondWithBadRequest($lang, 2);
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }
}
