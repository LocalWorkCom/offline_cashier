<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ReportServices\CashierPerformanceReportService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class CashierPerformanceReportsController extends Controller
{
    protected $cashierService;
    protected $lang;

    public function __construct(CashierPerformanceReportService $cashierService)
    {
        $this->lang =  app()->getLocale();

        $this->cashierService = $cashierService;
    }

    public function list(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'from' => 'nullable|date|date_format:Y-m-d',
            'to' => 'nullable|date|date_format:Y-m-d|after_or_equal:from',
            'cashier_id' => 'nullable|exists:employees,id',
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }

        $data = $this->cashierService->listCashiers($request);

        $response = paginateOrGetAll($data['cashiers'], $request);

        $response['data'] = [
            'cashiers' => $response['data'],
            'allCashiers' => $data['allCashiers']->get(),
        ];

        return ResponseWithSuccessDataPaginated($lang, $response, 1);
    }

    public function show($id)
    {
        $cashier = $this->cashierService->showCashier($id);
        if(!$cashier){
            $message = $this->lang === 'ar' ? 'المحاسب غير موجود.' : 'Cashier not found.';
            return respondError($message, 404);
        }
        return ResponseWithSuccessData($this->lang, $cashier, 1);
    }
}
