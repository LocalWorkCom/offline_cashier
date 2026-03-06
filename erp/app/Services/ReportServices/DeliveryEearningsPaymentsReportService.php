<?php

namespace App\Services\ReportServices;

use App\Models\Order;
use App\Models\Branch;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class DeliveryEearningsPaymentsReportService
{
    public function index(Request $request)
    {
        try {
            $lang = app()->getLocale();
            $admin = auth('admin')->user();
            $employee = auth('employee')->user();
            if (!$admin && !$employee) return RespondWithBadRequest($lang, 4);
            App::setLocale($lang);

            $timetables = Timetable::select('id', 'name_ar', 'name_en')->get();
            $selectedTimetableId = $request->input('timetable_id');

            $orders = Order::where('type', 'Delivery')
                ->where('status', 'completed')
                ->whereNotNull('delivery_id')
                ->with([
                    'delivery:id,first_name,last_name,phone_number',
                    'delivery.employeeSchedules.shiftDetails',
                    'branch:id,name_ar,name_en',
                    'orderTransactions:id,order_id,payment_method,paid'
                ])
                ->when($request->filled('branch_id'), fn($q) => $q->where('branch_id', $request->branch_id))
                ->when($request->filled('from_date'), fn($q) => $q->whereDate('date', '>=', $request->from_date))
                ->when($request->filled('to_date'), fn($q) => $q->whereDate('date', '<=', $request->to_date))
                ->when($request->filled('order_number'), function ($q) use ($request) {
                    $q->where('order_number', 'like', '%' . $request->order_number . '%')
                        ->orWhere('id', $request->order_number);
                })
                ->when($request->filled('payment_method'), function ($q) use ($request) {
                    $q->whereHas('orderTransactions', function ($query) use ($request) {
                        $query->where('payment_method', $request->payment_method);
                    });
                })
                ->when($request->filled('delivery_id'), fn($q) => $q->where('delivery_id', $request->delivery_id))
                ->when($request->filled('delivery_phone'), function ($q) use ($request) {
                    $q->whereHas('delivery', function ($query) use ($request) {
                        $query->where('phone', 'like', '%' . $request->delivery_phone . '%');
                    });
                })
                ->when($request->filled('min_total_price'), function ($q) use ($request) {
                    $q->whereHas('orderTransactions', function ($query) use ($request) {
                        $query->selectRaw('SUM(paid) as total_paid')
                            ->groupBy('order_id')
                            ->havingRaw('SUM(paid) >= ?', [$request->min_total_price]);
                    });
                })
                ->when($request->filled('max_total_price'), function ($q) use ($request) {
                    $q->whereHas('orderTransactions', function ($query) use ($request) {
                        $query->selectRaw('SUM(paid) as total_paid')
                            ->groupBy('order_id')
                            ->havingRaw('SUM(paid) <= ?', [$request->max_total_price]);
                    });
                })
                ->when($selectedTimetableId, function ($query) use ($selectedTimetableId) {
                    $query->whereHas('delivery.employeeSchedules.shiftDetails', function ($q) use ($selectedTimetableId) {
                        $q->where('timetable_id', $selectedTimetableId);
                    });
                })
                ->get();

            // Rest of your controller logic remains the same...
            $DeliveryReport = $orders->groupBy('delivery_id')->map(function ($orders, $deliveryId) use ($lang) {
                $delivery = $orders->first()->delivery;
                $totalOrders = $orders->count();
                $totalEarnings = $orders->sum(function ($order) {
                    return $order->orderTransactions->sum('paid');
                });
                $completedOrders = $orders->map(function ($order) use ($lang) {
                    $orderTotal = $order->orderTransactions->sum('paid');
                    $paymentMethods = $order->orderTransactions->pluck('payment_method')
                        ->unique()
                        ->map(function ($method) {
                            return __('report.payment_methods.' . $method);
                        })
                        ->implode(' - ');

                    return [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number ?? $order->id,
                        'branch_name' => $lang === 'ar'
                            ? ($order->branch->name_ar ?? '-')
                            : ($order->branch->name_en ?? '-'),
                        'total_price' => number_format($orderTotal, 2),
                        'payment_method' => $paymentMethods ?: '-',
                        'date' => $order->date,
                    ];
                })->toArray();

                return [
                    'delivery_id' => $deliveryId,
                    'first_name' => $delivery->first_name ?? '-',
                    'last_name' => $delivery->last_name ?? '-',
                    'phone' => $delivery->phone_number ?? '-',
                    'total_orders' => $totalOrders,
                    'total_earnings' => number_format($totalEarnings, 2),
                    'completed_orders' => $completedOrders,
                ];
            })->toArray();
            // Calculate correct totals
            $totalCompletedOrders = $orders->count(); // Count all completed orders
            $totalEarnings = $orders->sum(function ($order) {
                return $order->orderTransactions->sum('paid');
            });
            return ResponseWithSuccessData($lang, [
                'DeliveryReport' => $DeliveryReport,
                'timetables' => $timetables,
                'selectedTimetableId' => $selectedTimetableId,
                'branches' => Branch::select('id', 'name_ar', 'name_en')->get(),
                'totals' => [
                    'totalCompletedOrders' => $totalCompletedOrders,
                    'TotalEarnings' => $totalEarnings // Remove number_format here
                ]
            ], 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $lang = app()->getLocale();
            $admin = auth('admin')->user();
            $employee = auth('employee')->user();
            if (!$admin && !$employee) {
                return RespondWithBadRequest($lang, 4);
            }

            App::setLocale($lang);

            $orders = Order::where('type', 'Delivery')
                ->where('delivery_id', $id)
                ->where('status', 'completed') // Changed from 'cancelled' to 'completed'
                ->whereNotNull('delivery_id')
                ->with([
                    'delivery:id,first_name,last_name',
                    'branch:id,name_ar,name_en',
                    'orderTransactions:id,order_id,payment_method,paid' // Added order transactions
                ])
                ->when($request->filled('from_date'), fn($q) => $q->whereDate('date', '>=', $request->from_date))
                ->when($request->filled('to_date'), fn($q) => $q->whereDate('date', '<=', $request->to_date))
                ->get();

            if ($orders->isEmpty()) {
                return respondError(__('No completed orders found for this Delivery'), 404);
            }

            // Collecting Delivery data and order details
            $delivery = $orders->first()->delivery;
            // $totalEarnings = $orders->sum('total_price_after_tax');
            $totalEarnings = $orders->sum(function ($order) {
                return $order->orderTransactions->sum('paid');
            });
            $completedOrders = $orders->map(function ($order) use ($lang) {
                // Get all payment methods for this order
                $orderTotal = $order->orderTransactions->sum('paid');

                $paymentMethods = $order->orderTransactions->pluck('payment_method')
                    ->unique()
                    ->map(function ($method) {
                        return __('report.payment_methods.' . $method);
                    })
                    ->implode(' - ');
                return [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number ?? $order->id,
                    'branch_name' => $lang === 'ar'
                        ? ($order->branch->name_ar ?? '-')
                        : ($order->branch->name_en ?? '-'),
                    'total_price' => number_format($orderTotal, 2),
                    'payment_method' => $paymentMethods ?: '-',
                    'date' => $order->date,
                ];
            });

            // Structuring the data
            $data = [
                'delivery' => [
                    'delivery_id' => $delivery->id ?? '',
                    'first_name' => $delivery->first_name,
                    'last_name' => $delivery->last_name,
                    'total_orders' => $orders->count(),
                    'total_earnings' => number_format($totalEarnings, 2),
                    'completed_orders' => $completedOrders,
                ]
            ];

            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            logger("Error in show delivery orders: " . $e->getMessage());
            return respondError($e->getMessage(), 2);
        }
    }
}
