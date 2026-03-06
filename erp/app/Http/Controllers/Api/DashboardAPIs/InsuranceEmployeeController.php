<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Http\Requests\InsuranceEmployee\InsuranceEmployeeCreateRequest;
use App\Http\Requests\InsuranceEmployee\InsuranceEmployeeDeleteRequest;
use App\Http\Requests\InsuranceEmployee\InsuranceEmployeeListRequest;
use App\Http\Requests\InsuranceEmployee\InsuranceEmployeeUpdateRequest;
use App\Http\Resources\InsuranceEmployeeResource;
use App\Repositories\InsuranceEmployeeRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class InsuranceEmployeeController extends Controller
{
    public function __construct(
        readonly private InsuranceEmployeeRepositoryInterface $insuranceEmployeeRepository,
    )
    {}

    /**
     * Display a listing of the resource.
     */
    public function index(InsuranceEmployeeListRequest $request): JsonResponse
    {
        try {
            $items = $request->getPerPage() ?
                $this->insuranceEmployeeRepository->paginate(
                    $request->getSort(), $request->getPerPage(), $request->getFilters()
                ) :
                $this->insuranceEmployeeRepository->all();

            return ResponseWithSuccessData(
                lang: request()->header('lang', 'ar'),
                data: new InsuranceEmployeeResource($items),
                code: 1
            );
        } catch (\Throwable $e) {
            Log::error($e->getMessage() . ' - File: ' . $e->getFile() . ' - Line: ' . $e->getLine());
            return RespondWithBadRequest(request()->header('lang', 'ar'), 2);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InsuranceEmployeeCreateRequest $request): JsonResponse
    {
        try {
            $item = $this->insuranceEmployeeRepository->create(
                array_merge($request->safe()->toArray(), ['created_by' => auth('employee')->id()])
            );

            return ResponseWithSuccessData(
                lang: request()->header('lang', 'ar'),
                data: new InsuranceEmployeeResource($item),
                code: 1
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage() . ' - File: ' . $e->getFile() . ' - Line: ' . $e->getLine());
            return RespondWithBadRequest(request()->header('lang', 'ar'), 2);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InsuranceEmployeeUpdateRequest $request): JsonResponse
    {
        try {
            $item = $this->insuranceEmployeeRepository->update(
                ['id' => $request->getId()],
                array_merge($request->safe()->except('insurance_employee_id'), ['modified_by' => auth('employee')->id()])
            );

            return ResponseWithSuccessData(
                lang: request()->header('lang', 'ar'),
                data: new InsuranceEmployeeResource($item),
                code: 1
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage() . ' - File: ' . $e->getFile() . ' - Line: ' . $e->getLine());
            return RespondWithBadRequest(request()->header('lang', 'ar'), 2);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InsuranceEmployeeDeleteRequest $request): JsonResponse
    {
        $this->insuranceEmployeeRepository->delete($request->getId());
        return ResponseWithSuccessData(
            lang: request()->header('lang', 'ar'),
            data: "",
            code: 1
        );
    }
}
