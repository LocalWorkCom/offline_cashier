<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginationListRequest;
use App\Http\Resources\InsuranceEmployeeLogResource;
use App\Repositories\InsuranceEmployeeLogRepositoryInterface;
use Illuminate\Http\JsonResponse;

class InsuranceEmployeeLogController extends Controller
{
    public function __construct(
        readonly private InsuranceEmployeeLogRepositoryInterface $insuranceEmployeeLogRepository
    )
    {}

    public function index(PaginationListRequest $request): JsonResponse
    {
        $items = $request->getPerPage() ?
            $this->insuranceEmployeeLogRepository->paginate($request->getSort(), $request->getPerPage()) :
            $this->insuranceEmployeeLogRepository->all();

        return ResponseWithSuccessData(
            lang: request()->header('lang', 'ar'),
            data: new InsuranceEmployeeLogResource($items),
            code: 1
        );
    }
}
