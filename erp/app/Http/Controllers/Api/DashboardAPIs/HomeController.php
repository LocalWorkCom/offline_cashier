<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Services\DeliveryComplaintsService;
use App\Services\HomeService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $homeService;

    public function __construct(HomeService $homeService)
    {
        $this->homeService = $homeService;
    }

    public function index(Request $request)
    {

        $lang = $request->header('lang', 'ar');
        $response = $this->homeService->getDashboardData($lang);

        $responseData = $response->original;

        $data = $responseData['data'];

        return ResponseWithSuccessData($lang, $data, 1);
    }
}
