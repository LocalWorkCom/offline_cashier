<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TableReservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ClientServices\OrderService;


class OrderController extends Controller
{
    protected $orderService;
    protected $lang;
    protected $checkToken;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
        $this->lang =  app()->getLocale();
        $this->checkToken = false;
    }
    public function trackOrder(Request $request)
    {
        $user = Auth::guard('client')->user();
        $branchId = $request->cookie('branch_id') ?? ($_COOKIE['branch_id'] ?? null);

        $orders = Order::with([
            'client',
            'branch.country',
            'address',
            'tracking',
            'orderDetails',
            'orderDetails.dishSize',
            'orderAddons.Addon.addons',
            'orderProducts',
            'orderTransactions',
            'coupon'
        ])
            ->where('client_id', $user->id)
            ->where('branch_id', $branchId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('created_at', 'desc')
            ->get();

        $now = Carbon::now();

        $reservations = TableReservation::where(function ($query) use ($now) {
            $query->whereDate('date', '>', $now->toDateString())
                ->orWhere(function ($query) use ($now) {
                    $query->whereDate('date', '>=', $now->toDateString())
                        ->whereTime('time_from', '>=', $now->toTimeString());
                });
        })
            ->with([
                'client',
                'tables',
                'branch',
                'order',
                'floorPartition'
            ])
            ->where('client_id', $user->id)
            ->where('branch_id', $branchId)
            ->where('status', 'confirm')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('website.track-order', compact('orders', 'reservations'));
    }
    public function pastOrders(Request $request)
    {
        $user = Auth::guard('client')->user();
        $branchId = $request->cookie('branch_id') ?? ($_COOKIE['branch_id'] ?? null);

        $orders = Order::with([
            'branch.country',
            'address',
            'tracking',
            'orderDetails',
            'orderDetails.dishSize',
            'orderAddons.Addon',
            'orderProducts',
            'orderTransactions',
            'coupon'
        ])
            ->where('client_id', $user->id)
            ->where('branch_id', $branchId)
            ->orderBy('created_at', 'desc')
            ->get();

        $reservations = TableReservation::with([
            'client',
            'tables',
            'branch',
            'order',
        ])
            ->where('client_id', $user->id)
            ->where('branch_id', $branchId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('website.orders', compact('orders', 'reservations'));
    }

    public function paymentDetails($id)
    {
        $user = Auth::guard('client')->user();

        $order =  Order::with([
            'client',
            'branch.country',
            'address',
            'tracking',
            'orderDetails',
            'orderAddons.Addon',
            'orderProducts',
            'orderTransactions',
            'coupon',
        ])
            ->where('client_id', $user->id)
            ->where('id', $id)
            ->first();

        $reservation = TableReservation::with([
            'client',
            'tables',
            'branch',
            'order',
        ])
            ->where('client_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->where('id', $id)
            ->first();

        return view('website.order-payment-details', compact('order', 'reservation'));
    }
    public function validateCancellation(Request $request)
    {
        $order = Order::find($request->order_id);

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => __('validation.orderNotFound'),
            ]);
        }

        $cancellableUntil = $order->created_at->addMinutes($order->branch->time_cancellation);

        if (now()->greaterThan($cancellableUntil)) {
            return response()->json([
                'status' => 'error',
                'message' => __('validation.cannotCancelAfterTime'),
            ]);
        }
        if ($order->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => __('validation.cannotCancelAfterPending'),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => __('validation.cancellationAllowed'),
        ]);
    }
    public function cancelOrder(Request $request)
    {
        $orderId = $request->input('order_id');

        $order = Order::with(['tracking' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->find($orderId);

        $lastTracking = $order->tracking->last();
        $lastStatus = $lastTracking?->order_status ?? 'pending';

        if ($order->status === 'pending') {
            $order->update([
                'status' => 'cancelled',
                'print_status' => 'cancelled'
            ]);
            $order->tracking()->create([
                'order_id' => $order->id,
                'order_status' => 'cancelled'
            ]);

            return response()->json(['status' => 'success', 'message' => __('validation.cancelOrder')]);
        }
    }
    public function reOrder(Request $request)
    {
        $response = $this->orderService->reOrder($request, false);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        return response()->json(['status' => 'success', 'data' => $responseData['data']]);
    }
}
