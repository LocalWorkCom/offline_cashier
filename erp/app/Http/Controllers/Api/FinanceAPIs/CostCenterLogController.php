<?php

namespace App\Http\Controllers\Api\Finance;

use App\Helper\APIResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaginationListRequest;
use App\Http\Resources\CostCenterLogResource;
use App\Repositories\CostCenterLogRepositoryInterface;
use Illuminate\Http\JsonResponse;

class CostCenterLogController extends Controller
{
    public function __construct(
        readonly private CostCenterLogRepositoryInterface $costCenterLogRepository
    )
    {}

    public function index(PaginationListRequest $request): JsonResponse
    {
        $items = $request->getPerPage() ?
            $this->costCenterLogRepository->paginate($request->getSort(), $request->getPerPage()) :
            $this->costCenterLogRepository->all();

        return ResponseWithSuccessData(
            lang: request()->header('lang', 'ar'),
            data: new CostCenterLogResource($items),
            code: 1
        );
    }
}
