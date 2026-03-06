<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Helper\APIResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaginationListRequest;
use App\Http\Resources\InsuranceLogResource;
use App\Repositories\InsuranceLogRepositoryInterface;
use Illuminate\Http\JsonResponse;

class InsuranceLogController extends Controller
{
    public function __construct(
        readonly private InsuranceLogRepositoryInterface $insuranceLogRepository
    )
    {}

    public function index(PaginationListRequest $request): JsonResponse
    {
        $items = $request->getPerPage() ?
            $this->insuranceLogRepository->paginate($request->getSort(), $request->getPerPage()) :
            $this->insuranceLogRepository->all();

        return ResponseWithSuccessData(
            lang: request()->header('lang', 'ar'),
            data: new InsuranceLogResource($items),
            code: 1
        );
    }
}
