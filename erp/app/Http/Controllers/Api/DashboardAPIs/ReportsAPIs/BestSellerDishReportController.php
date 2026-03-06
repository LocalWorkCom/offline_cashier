<?php

namespace App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Services\ReportServices\BestSellerDishReportService;
use Illuminate\Support\Facades\Log;

class BestSellerDishReportController extends Controller
{
    protected $bestSellerDishReportService;
    protected $lang;

    public function __construct(BestSellerDishReportService $bestSellerDishReportService)
    {
        $this->bestSellerDishReportService = $bestSellerDishReportService;
        $this->lang = app()->getLocale();
    }

    /**
     * Get best seller dishes report with filtering
     */
    public function index(Request $request)
    {
        $lang = app()->getLocale();

        $response = $this->bestSellerDishReportService->index($request);
        $response = paginateOrGetAll($response, $request, []);

        return ResponseWithSuccessDataPaginated($lang, $response, 1);
    }

    /**
     * Get detailed report for a specific dish
     */
    public function show($id)
    {
        $lang = app()->getLocale();

        try {
            $report = $this->bestSellerDishReportService->show($id);

            if (!$report['dish']) {
                $message = $lang == 'en' ? 'Dish not found' : 'الطبق غير موجود';
                return respondError($message, 404);
            }
            $data = [
                'dish' => $report['dish'],
                'branches' => $report['branches'],
                'addons' => $report['addons'],
                'totals' => $report['totals']
            ];
            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching best seller dish details: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
