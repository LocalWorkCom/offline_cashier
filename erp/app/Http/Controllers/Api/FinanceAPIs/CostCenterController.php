<?php

namespace App\Http\Controllers\Api\FinanceAPIs;

use App\Helper\APIResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreateCostCenterRequest;
use App\Http\Requests\Finance\UpdateCostCenterRequest;
use App\Http\Requests\Finance\ComparisonCostCenterRequest;
use App\Http\Resources\Finance\CostCenterResource;
use App\Services\FinanceServices\CostCenterService;
use App\Models\CostCenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CostCenterController extends Controller
{

    protected $costCenterService;

    public function __construct(CostCenterService $costCenterService)
    {
        $this->costCenterService = $costCenterService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        $data = $this->costCenterService->getAll($request);
        $response = paginateOrGetAll($data, $request, [], []);

        return new CostCenterResource([
            'data' => $response['data'],
            'meta' => $response['meta'],
            'status' => true,
            'message' => ApiCode(1)->{'api_code_message_' . ($lang == 'ar' ? 'ar' : 'en')},
            'code' => 200,
        ]);
    }

    public function store(CreateCostCenterRequest $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            $costCenter = $this->costCenterService->add($request);
            return ResponseWithSuccessData($lang, new CostCenterResource($costCenter), 1);
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
        return $data = $this->costCenterService->show(request());
    }

    public function update(UpdateCostCenterRequest $request, $id)
    {
        $lang = $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            return $costCenter = $this->costCenterService->edit($request);
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
        return $data = $this->costCenterService->delete(request());
    }

    public function comparison(ComparisonCostCenterRequest $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            return $costCenter = $this->costCenterService->comparison($request);
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

    public function archive(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }

            return $journal = $this->costCenterService->archive($request);
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

    public function showList()
    {
        $lang = request()->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }

        $validator = Validator::make(request()->all(), [
            'facility_id' => 'required|integer|exists:facilities,id',
        ], [
            'facility_id.required' => $lang == 'en' ? 'Facility is required' : 'المنشأة مطلوبة',
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation error' : 'خطأ في التحقق',
                400,
                $validator->errors()->all()
            );
        }

        return $data = $this->costCenterService->showList(request());
    }
}
