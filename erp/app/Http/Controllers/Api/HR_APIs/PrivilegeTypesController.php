<?php

namespace App\Http\Controllers\API\HR_APIs;

use App\Http\Controllers\Controller;
use App\Services\HR_Services\PrivilegeTypeService;
use Illuminate\Http\Request;

class PrivilegeTypesController extends Controller
{
    protected $PrivilegeTypeService;

    public function __construct(PrivilegeTypeService $PrivilegeTypeService)
    {
        $this->PrivilegeTypeService = $PrivilegeTypeService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            $data = $this->PrivilegeTypeService->index($request);
            $visible = ['name', 'reason'];
            $fields = ['name_ar', 'name_en', 'name_site', 'reason_ar', 'reason_en'];
            $response = paginateOrGetAll($data, $request, $fields, $visible);
            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $data = $this->PrivilegeTypeService->show($request, $id);
            if (!$data) {
                $message = $lang === 'ar' ? 'العنصر  غير موجود' : 'This item is not found';
                return respondError($message, 404);
            }
            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function add(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            return $response = $this->PrivilegeTypeService->add($request);
        } catch (\Exception $e) {
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }
    public function edit(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            return $response = $this->PrivilegeTypeService->edit($request, $id);
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
            return $response = $this->PrivilegeTypeService->delete($request, $id);
        } catch (\Exception $e) {
            // return RespondWithBadRequest($lang, 2);
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }
}
