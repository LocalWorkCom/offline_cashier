<?php

namespace App\Http\Controllers\Api\FinanceAPIs;

use App\Helper\APIResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreateFacilityRequest;
use App\Http\Requests\Finance\UpdateFacilityRequest;
use App\Http\Resources\Finance\FacilityResource;
use App\Services\FinanceServices\FacilityService;
use App\Models\Facility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class FacilityController extends Controller
{

    protected $facilityService;

    public function __construct(FacilityService $facilityService)
    {
        $this->facilityService = $facilityService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        $data = $this->facilityService->getAll($request);
        $response = paginateOrGetAll($data, $request, [], []);

        return new FacilityResource([
            'data' => $response['data'],
            'meta' => $response['meta'],
            'status' => true,
            'message' => ApiCode(1)->{'api_code_message_' . ($lang == 'ar' ? 'ar' : 'en')},
            'code' => 200,
        ]);
    }

    public function store(CreateFacilityRequest $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }

            $facility = $this->facilityService->add($request);
            return ResponseWithSuccessData($lang, new FacilityResource($facility), 1);
        } catch (\Exception $e) {
            return respondError(
                $lang == 'en' ? 'An error occurred' : 'حصل خطأ',
                400,
                $lang == 'en'
                    ? ['An error occurred during the addition process. Please try again.']
                    : ['حصل خطأ أثناء عملية الإضافة من فضلك حاول مرة أخرى']
            );
        }
    }

    public function show($id)
    {
        $lang = request()->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        return $data = $this->facilityService->show(request());
    }

    public function update(UpdateFacilityRequest $request, $id)
    {
        $lang = $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            return $facility = $this->facilityService->edit($request);
        } catch (\Exception $e) {
            return respondError(
                $lang == 'en' ? 'An error occurred' : 'حصل خطأ',
                400,
                $lang == 'en'
                    ? ['An error occurred during the addition process. Please try again.']
                    : ['حصل خطأ أثناء عملية الإضافة من فضلك حاول مرة أخرى']
            );
        }
    }

    public function destroy($id)
    {
        $lang = request()->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        return $data = $this->facilityService->delete(request());
    }
}
