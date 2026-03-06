<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Services\AdminServices\WaiterRequestService;
use App\Services\ClientServices\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WaiterRequestController extends Controller
{
    protected $waiterRequestService;
    protected $orderService;

    public function __construct(WaiterRequestService $waiterRequestService, OrderService $orderService)
    {
        $this->waiterRequestService = $waiterRequestService;
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $user = Auth::guard('employee')->user();

        $data = $this->waiterRequestService->index($user);
        $data = paginateOrGetAll($data, $request, null);
        $data['data'] = collect($data['data'])->map(function ($request)use($lang) {
            if ($request->type == 2) {
                $orderIds = is_array($request->order_ids)
                    ? $request->order_ids
                    : json_decode($request->order_ids, true);

                $request->new_order_id = (int) ($orderIds[0] ?? 0);
            }
            $request->type = match ($request->type) {
                0 => $lang == 'en' ? 'split' : 'فصل',
                1 => $lang == 'en' ? 'merge' : 'دمج',
                default => $lang == 'en' ? 'print' : 'طباعة',
            };
            $request->status = match ($request->status) {
                0 => $lang == 'en' ? 'pending'  : 'قيد الانتظار',
                1 => $lang == 'en' ? 'approved' : 'موافق عليه',
                default => $lang == 'en' ? 'rejected' : 'مرفوض',
            };
            return $request;
        });
        return ResponseWithSuccessDataPaginated($lang, $data, 1);
    }

    public function showInvoice(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        try {

            $data = $this->waiterRequestService->showInvoice($id);

            // if (!$data) {
            //     return RespondWithBadRequestWithData(__('order.RequestNotFound'));
            // }

            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'الطلب غير موجود' : ' The Request not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching recipe: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function rejectRequest(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $this->waiterRequestService->rejectRequest($request);

        return ResponseWithSuccessData($lang, $lang == 'en' ? 'Order rejected successfully' : 'تم رفض الطلب بنجاح', 1);
    }

    public function acceptRequest(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $result = $this->waiterRequestService->acceptRequest($id, $lang);

        if (!$result || (is_array($result) && isset($result['status']) && !$result['status'])) {
            return RespondWithBadRequestWithData($result['message'] ?? __('order.RequestNotFound'));
        }

        return ResponseWithSuccessData($lang, $lang == 'en' ? 'Order accepted successfully' : 'تم قبول الطلب بنجاح', 1);
    }

    public function ajaxPrint(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $orderId = $request->order_id;

        $response = $this->orderService->show($lang, $orderId, 1);
        $data = $this->waiterRequestService->ajaxPrint($orderId, $response);

        return ResponseWithSuccessData($lang, $data, 1);
    }

    public function divideInvoice($id)
    {
        $data = $this->waiterRequestService->divideInvoice($id);

        if (!$data) {
            return RespondWithBadRequestWithData(__('order.UnableToSplitInvoice'));
        }
        return ResponseWithSuccessData(App::getLocale(), $data, 1);
    }

    public function mergeInvoices($id)
    {
        try {
            $data = $this->waiterRequestService->mergeInvoices($id);
            return ResponseWithSuccessData(App::getLocale(), $data, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestWithData(__('order.SomethingWentWrong'));
        }
    }
}
