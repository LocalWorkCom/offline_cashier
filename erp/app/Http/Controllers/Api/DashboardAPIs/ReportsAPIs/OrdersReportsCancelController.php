<?php

namespace App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs;

use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Models\OrderTracking;
use App\Models\OrderTransaction;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryOrderCancelResource;
use App\Services\ReportServices\CancelledOrdersReportsService;

class OrdersReportsCancelController extends Controller
{
    protected $CancelledOrdersReportsService;

    protected $fields = [];
    protected $fields_visible = [];

    public function __construct(CancelledOrdersReportsService $CancelledOrdersReportsService)
    {
        $this->CancelledOrdersReportsService = $CancelledOrdersReportsService;
    }

    public function list(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);

        $response = $this->CancelledOrdersReportsService->listOrders($request, false);

        $responsePag = paginateOrGetAll($response, $request, null, null);
        
        if(empty($responsePag['data'])){
            return ResponseWithSuccessDataPaginated($lang, ['data' => [], 'meta' => $responsePag['meta']], 1);
        }
        $responseData['data'] = $responsePag['data']->map(function ($item) {

                if ($item->status === 'cancelled' && $item->cancellationReasons->isNotEmpty()) {
                    $reasons = $item->cancellationReasons->pluck('localized_reason')->implode(', ');
                    // $types = $item->cancellationReasons->pluck('type')->implode(', ');
                    $dataTypes = $item->cancellationReasons->pluck('type');
                    $types = collect($dataTypes)->map(function ($dataType) {
                        return __('cancellation_reasons.' . strtolower($dataType));
                    })->implode(', ');
                    $users = $item->cancellationReasons->map(function ($r) {
                        return $r->user->name ?? 'N/A';
                    })->implode(', ');
                } else {
                    $reasons = '-----';
                    $types = '-----';
                    $users = '-----';
                }

            return [
                'id' => $item->id,
                'invoice_number' => $item->invoice_number,
                'date' => $item->date,
                'type' => __('order.' . strtolower($item->type)), //$item->type,
                'branch_name' => $item->branch->name ?? '-----',
                'client' => $item->responsible_person ?? '-----',
                'price' => ($item->total_price_after_tax ?? 0) . ' ' . ($item->branch->country->currency_symbol ?? ''),
                'status' => __('order.' . $item->status),
                'payment_status' => __('order.' . strtolower($item->transaction->payment_status)), //$item->transaction->payment_status ?? '-----',
                'payment_method' => __('order.' . strtolower($item->transaction->payment_method)), //$item->transaction->payment_method ?? '-----',
                'cancellation_reasons' => $reasons,
                'cancellation_types' => $types,
                'cancelled_by' => $users,
            ];
        });

        $responseData['meta'] = $responsePag['meta'];

        return ResponseWithSuccessDataPaginated($lang, $responseData, 1);
    }


    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);

        $order = $this->CancelledOrdersReportsService->orderDetails($id);

        if (!$order) {
            return RespondWithBadRequest($lang, 2);
        }

        $transaction = $order->orderTransactions->first();
        $tracking = $order->tracking->first();
        $cancellationReason = $order->cancellationReasons->first();

        $orderDetails = [];
        if ($order->orderDetails) {
            foreach ($order->orderDetails as $detail) {
                $addons = $order->orderAddons->filter(
                    fn($addon) => $addon->order_details_id == $detail->id && $addon->Addon?->addons?->name_ar
                )->map(function ($addon) use ($lang) {
                    return [
                        'name' => $lang === 'ar' ? $addon->Addon->addons->name_ar : $addon->Addon->addons->name_en,
                        'price_before_tax' => $addon->price_before_tax,
                        'price_after_tax' => $addon->price_after_tax,
                    ];
                })->toArray();

                $orderDetails[] = [
                    'dish' => $detail->dish ? [
                        'name' => $lang === 'ar' ? $detail->dish->name_ar : $detail->dish->name_en,
                        'image' => $detail->dish->image ? asset($detail->dish->image) : asset('default-dish.jpg'),
                    ] : null,
                    'size' => $detail->dishSize ? ($lang === 'ar' ? $detail->dishSize->size_name_ar : $detail->dishSize->size_name_en) : __('order.nosize'),
                    'addons' => $addons ?: [__('order.noaddons')],
                    'quantity' => $detail->quantity,
                    'total_price' => $order->tax_application == 0
                        ? $detail->price_befor_tax + array_sum(array_column($addons, 'price_before_tax'))
                        : $detail->price_after_tax + array_sum(array_column($addons, 'price_after_tax')),
                    'currency_symbol' => $order->Branch->country->currency_symbol,
                    'note' => $detail->note ?? __('order.nonotes'),
                ];
            }
        }

        $clientDetails = $order->client ? [
            'name' => $order->responsible_person ?? __('order.unknown'),
            'email' => $order->responsible_person_email ?? __('order.unknown'),
            'phone' => $order->responsible_person_phone ?? __('order.unknown'),
            'flag' => $order->responsible_person_flag ?? __('order.unknown'),
        ] : ['error' => __('order.client_deleted')];

        $deliveryAddress = $order->address ? [
            'address' => $order->address->address ?? __('order.unknown'),
            'city' => $order->address->city ?? __('order.unknown'),
            'state' => $order->address->state ?? __('order.unknown'),
            'zip' => $order->address->postal_code ?? __('order.unknown'),
        ] : null;

        $paymentSummary = [
            'sub_total' => $order->tax_application == 0
                ? $order->total_price_befor_tax
                : $order->total_price_befor_tax + $order->tax_value,
            'currency_symbol' => $order->Branch->country->currency_symbol,
            'delivery_fees' => $order->delivery_fees ?? 0,
            'service_fees' => $order->service_fees ?? 0,
            'tax_percentage' => $order->total_price_befor_tax > 0
                ? number_format(($order->tax_value / $order->total_price_befor_tax) * 100, 2) . '%'
                : '0%',
            'total' => number_format($order->total_price_after_tax, 2),
        ];

        if ($order->coupon_id) {
            $paymentSummary['coupon'] = [
                'type' => $order->coupon->type,
                'value' => $order->coupon->type === 'percentage'
                    ? - ($order->total_price_befor_tax * $order->coupon->value) / 100
                    : -$order->coupon->value,
            ];
        }

        $orderStatus = [
            'status' => $tracking ? __('order.' . strtolower($order->status ?? 'unknown')) : __('order.unknown'),
            'created_at' => $tracking ? $tracking->created_at->format('d-m-Y H:i') : null,
            'payment_method' => $transaction ? __('order.' . strtolower($transaction->payment_method ?? 'unknown')) : __('order.unknown'),
            'payment_status' => $transaction ? __('order.' . strtolower($transaction->payment_status ?? 'unknown')) : __('order.unknown'),
        ];

        $cancellationData = [];
        if ($cancellationReason) {
            $cancellationData = [
                'comment' => $cancellationReason->reason,
                'reasons' => $order->cancellationReasons->map(function ($reason) use ($lang) {
                    return $lang === 'ar' ? $reason->reasonModel->reason_ar : $reason->reasonModel->reason_en;
                })->toArray(),
            ];
        }

        $responseData = [
            'order_number' => $order->order_number,
            'order_details' => $orderDetails ?: [__('order.no_details')],
            'client_details' => $clientDetails,
            'delivery_address' => $deliveryAddress,
            'payment_summary' => $paymentSummary,
            'order_status' => $orderStatus,
            'cancellation' => $cancellationData,
            'order_notes' => $order->note ?? __('order.nonotes'),
        ];

        return ResponseWithSuccessData($lang, $responseData, 1);
    }
}
