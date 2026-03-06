<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\AdminServices\WaiterRequestService;
use App\Services\ClientServices\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class WaiterRequestController extends Controller
{
    protected $orderService;
    protected $waiterRequestService;

    public function __construct(OrderService $orderService, WaiterRequestService $waiterRequestService)
    {
        $this->orderService = $orderService;
        $this->waiterRequestService = $waiterRequestService;
    }

    public function index(Request $request)
    {
        $user = Auth::guard('admin')->user();
        $user->branch_id = getBranchManagerID();
        $requests = $this->waiterRequestService->index($user)->get();

        return view('dashboard.requests.index', compact('requests'));
    }

    public function ajaxPrint(Request $request)
    {
        $orderId = $request->order_id;
        $lang = App::getLocale();
        $response = $this->orderService->show($lang, $orderId,0);
        $order = $this->waiterRequestService->ajaxPrint($orderId, $response);

        $html = view('dashboard.requests.print', compact('order'))->render();
        return response()->json(['html' => $html]);
    }

    public function showInvoice($id)
    {
        $data = $this->waiterRequestService->showInvoice($id);

        if (!$data) {
            return response()->json(['message' => __('order.RequestNotFound')], 404);
        }

        return view('dashboard.requests.detail', compact('data'));
    }

    public function rejectRequest(Request $request)
    {
        $this->waiterRequestService->rejectRequest($request);
        return redirect()->route('waiterrequest.list')->with('success', __('order.RequestRejected'));
    }

    public function acceptRequest($id)
    {
        $result = $this->waiterRequestService->acceptRequest($id);

        if (!$result['status']) {
            return redirect()->back()->withErrors($result['message']);
        }

        return redirect()->route('waiterrequest.list')->with('success', __('order.RequestAccepted'));
    }

    public function divideInvoice($id)
    {
        $data = $this->waiterRequestService->divideInvoice($id);

        if (!$data) {
            return response()->json(['message' => __('order.UnableToSplitInvoice')], 400);
        }

        return response()->json($data);
    }

    public function mergeInvoices($id)
    {
        try {
            $data = $this->waiterRequestService->mergeInvoices($id);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => __('order.SomethingWentWrong')], 500);
        }
    }
}
