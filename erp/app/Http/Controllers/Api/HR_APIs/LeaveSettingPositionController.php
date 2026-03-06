<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use App\Models\LeaveSettingPosition;
use App\Services\HR_Services\LeaveSettingPositionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LeaveSettingPositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $leaveSettingPositionService;

    public function __construct(LeaveSettingPositionService $leaveSettingPositionService)
    {
        $this->leaveSettingPositionService = $leaveSettingPositionService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            $data = $this->leaveSettingPositionService->index($request);
            $hidden = [];
            $visible = [];
            $response = paginateOrGetAll($data, $request, $hidden, $visible);
            return ResponseWithSuccessDataPaginated($lang, $response, 1);
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
            return $response = $this->leaveSettingPositionService->add($request);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }

    public function edit(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            return $response = $this->leaveSettingPositionService->edit($request, $request->id);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
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
            return $response = $this->leaveSettingPositionService->delete($request, $request->id);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }
    }
}
