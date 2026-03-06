<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Services\ClientServices\OrderService;
use App\Services\ComplaintsService;
use GPBMetadata\Google\Protobuf\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class OrderComplaintsController extends Controller
{
    protected $complaintsService;

    protected $orderService;

    public function __construct(OrderService $orderService, ComplaintsService $complaintsService)
    {
        $this->complaintsService = $complaintsService;
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $response = $this->complaintsService->index($request, 1);

        $responseData = $response->original;

        $complaints = $responseData['data'];

        return ResponseWithSuccessDataPaginated($lang, $complaints, 1);
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $response = $this->complaintsService->show($request, $id, 1);

        // Check if the response is an error response
        if ($response->getStatusCode() != 200) {
            return $response; // Return the error response as-is
        }

        // Only process if it's a successful response
        $responseData = $response->original;
        $complaint = $responseData['data'];
        return ResponseWithSuccessData($lang, $complaint, 1);
    }

    // public function edit($id)
    // {
    //     $lang = $request->header('lang', 'ar');
    //     $complaint = Complaint::findOrFail($id);

    //     return ResponseWithSuccessData($lang, $complaint, 1);
    // }

    public function changeStatus(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $response = $this->complaintsService->changeStatus($request, $id, 1);
        $responseData = $response->original;
       // Check if the response is an error response
        if ($response->getStatusCode() != 200) {
            return $response; // Return the error response as-is
        }
        $message = $responseData['message'];
        return ResponseWithSuccessData($lang, $message, 1);
        // return redirect('dashboard/complaints')->with('message',$message);
    }

    public function delete(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $response = $this->complaintsService->destroy($request, $id, 1);

        // Check if the response is an error response
        if ($response->getStatusCode() != 200) {
            return $response; // Return the error response as-is
        }

        // Only process if it's a successful response
        $responseData = $response->original;
        return ResponseWithSuccessData($lang, $responseData['data'], 1);
    }

    public function changeOrderTable(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();
        $created_by = $employee->id;

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'table_id' => 'required|exists:tables,id',
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $result = $this->orderService->changeOrderTable($request->table_id, $request->order_id);

        if ($result === true) {
            return ResponseWithSuccessData($lang, $lang == 'en' ? 'Order table changed successfully' : 'تم تغير طاوله الطلب بنجاح', 1);
        }

        if ($result instanceof \Illuminate\Http\JsonResponse) {
            $data = $result->getData(true);
            return RespondWithBadRequestWithData($data['message'] ?? __('validation.cannotchange'));
        }

        return RespondWithBadRequestWithData(__('validation.cannotchange'));
    }
}
