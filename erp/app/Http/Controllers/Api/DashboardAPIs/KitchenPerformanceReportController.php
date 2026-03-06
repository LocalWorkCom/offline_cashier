<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\KitchenServices\KitchenStaffService;


class KitchenPerformanceReportController extends Controller
{
    protected $KitchenStaffService;

    protected $lang;


    public function __construct(KitchenStaffService $KitchenStaffService)
    {
        $this->lang =  app()->getLocale();
        $this->KitchenStaffService = $KitchenStaffService;
    }
    public function index(Request $request)
    {
        $results = $this->KitchenStaffService->listRequests($request);
        // $data = paginateOrGetAll(collect($results['data']), $request, []);

        return ResponseWithSuccessData($this->lang, [
            'totals' => $results['totals'],
            'data'   => $results['data'],
            'meta'   => $results['meta'],
        ], 1);
    }

    public function show(Request $request, $id)
    {
        $results = $this->KitchenStaffService->listRequests($request, $id);
        if (!$results['data']) {
            return respondError($this->lang == 'en' ? 'kitchen performance not found.' : 'اداء المطبخ غير موجود', 404);
        }
        // $data = paginateOrGetAll(collect($results['data']), $request, []);

        return ResponseWithSuccessData($this->lang, [
            'totals' => $results['totals'],
            'data'   => $results['data'],
            'meta'   => $results['meta'],
        ], 1);
    }

    public function search(Request $request)
    {
        $search = $request->all();
        $results = $this->KitchenStaffService->searchRequests($search);
        $data = paginateOrGetAll($results, $request, []);
        return ResponseWithSuccessData($this->lang, $data, 1);
    }
}
