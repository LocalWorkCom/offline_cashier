<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Services\DeliveryComplaintsService;
use Illuminate\Http\Request;

class DeliveryComplaintsController extends Controller
{
    protected $complaintsService;

    public function __construct(DeliveryComplaintsService $complaintsService)
    {
        $this->complaintsService = $complaintsService;
    }

    public function index(Request $request)
    {
         $lang = $request->header('lang', 'ar');
//        dd(app()->getLocale());
        $response = $this->complaintsService->index($request, 1);

        $responseData = $response->original;

        $complaints = $responseData['data'];

        return ResponseWithSuccessData($lang, $complaints, 1);
    }

    public function show(Request $request, $id)
    {
         $lang = $request->header('lang', 'ar');
        return $response = $this->complaintsService->show($request, $id, 1);
        if ($response->getStatusCode() != 200) {
            return $response; // Return the error response as-is
        }

        $responseData = $response->original;
        $complaint = $responseData['data'];
        return ResponseWithSuccessData($lang, $complaint, 1);
    }

    public function changeStatus(Request $request, $id)
    {
         $lang = $request->header('lang', 'ar');
        $response = $this->complaintsService->changeStatus($request, $id, 1);
        $responseData = $response->original;
         if ($response->getStatusCode() != 200) {
            return $response; // Return the error response as-is
        }
        $message= $responseData['message'];
        return ResponseWithSuccessData($lang, $message, 1);
    }

    public function delete(Request $request, $id)
    {
         $lang = $request->header('lang', 'ar');
        $response = $this->complaintsService->destroy($request, $id, 1);
        if ($response->getStatusCode() != 200) {
            return $response; // Return the error response as-is
        }

        $responseData = $response->original;
        $message= $responseData['message'];
        return ResponseWithSuccessData($lang, $message, 1);
    }
}
