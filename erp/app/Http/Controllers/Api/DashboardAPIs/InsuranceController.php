<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\InsuranceCreateRequest;
use App\Http\Requests\Insurance\InsuranceDeleteRequest;
use App\Http\Requests\Insurance\InsuranceUpdateRequest;
use App\Http\Requests\PaginationListRequest;
use App\Http\Resources\InsuranceResource;
use App\Repositories\InsuranceRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class InsuranceController extends Controller
{
    public function __construct(
        readonly private InsuranceRepositoryInterface $insuranceRepository,
    )
    {}

    /**
     * Display a listing of the resource.
     */
    public function index(PaginationListRequest $request): JsonResponse
    {
        // try {
            $items = $request->getPerPage() ?
                $this->insuranceRepository->paginate(
                    $request->getSort(), $request->getPerPage()
                    // ['createdByUser', 'modifiedByUser', 'deletedByUser']
                ) :
                $this->insuranceRepository->all();

            return ResponseWithSuccessData(
                lang: request()->header('lang', 'ar'),
                data: new InsuranceResource($items),
                code: 1
            );
        // } catch (\Throwable $e) {
        //     Log::error($e->getMessage() . ' - File: ' . $e->getFile() . ' - Line: ' . $e->getLine());
        //     return RespondWithBadRequest(request()->header('lang', 'ar'), 2);
        // }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InsuranceCreateRequest $request): JsonResponse
    {
        try {
            $item = $this->insuranceRepository->create(
                array_merge($request->safe()->toArray(), ['created_by' => auth('employee')->id()])
            );

            return ResponseWithSuccessData(
                lang: request()->header('lang', 'ar'),
                data: new InsuranceResource($item),
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
    public function update(InsuranceUpdateRequest $request): JsonResponse
    {
        try {
            $item = $this->insuranceRepository->update(
                ['id' => $request->getId()],
                array_merge($request->safe()->except('insurance_id'), ['modified_by' => auth('employee')->id()])
            );

            return ResponseWithSuccessData(
                lang: request()->header('lang', 'ar'),
                data: new InsuranceResource($item),
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
    public function destroy(InsuranceDeleteRequest $request): JsonResponse
    {
        $this->insuranceRepository->delete($request->getId());
        return ResponseWithSuccessData(
            lang: request()->header('lang', 'ar'),
            data: "",
            code: 1
        );
    }
}
