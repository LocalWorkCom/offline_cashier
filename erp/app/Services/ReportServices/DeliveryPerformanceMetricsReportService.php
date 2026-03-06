<?php

namespace App\Services\ReportServices;

use App\Models\Area;
use App\Models\Order;
use App\Models\Branch;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DeliveryPerformanceMetricsReportService
{
    public function index(Request $request)
    {
        try {
            $lang = app()->getLocale();
            $employee = auth('employee')->user();
            $admin = auth('admin')->user();
            $user = $admin ?? $employee;
            if (!$user) return RespondWithBadRequest($lang, 4);
            App::setLocale($lang);

            $timetables = Timetable::select('id', 'name_ar', 'name_en')->get();
            $selectedTimetableId = $request->input('timetable_id');

            $orders = Order::where('type', 'Delivery')
                ->whereIn('status', ['cancelled', 'onHold', 'completed'])
                ->whereNotNull('delivery_id')
                ->with([
                    'delivery:id,first_name,last_name,phone_number',
                    'branch:id,name_ar,name_en,delivery_time',
                    'deliveryComplaints:id,order_id,message',
                    'tracking' => function ($q) {
                        $q->where('order_status', 'on_way')->orderBy('created_at', 'asc');
                    },
                ])
                ->when($request->filled('branch_id'), fn($q) => $q->where('branch_id', $request->branch_id))
                ->when($request->filled('area_id'), function ($q) use ($request) {
                    $q->whereHas('branch', function ($query) use ($request) {
                        $query->where('area_id', $request->area_id);
                    });
                })
                ->when($request->filled('from_date'), fn($q) => $q->whereDate('date', '>=', $request->from_date))
                ->when($request->filled('to_date'), fn($q) => $q->whereDate('date', '<=', $request->to_date))
                ->when($request->filled('order_number'), fn($q) => $q->where('order_number', 'like', '%' . $request->order_number . '%'))
                ->when($selectedTimetableId, function ($query) use ($selectedTimetableId) {
                    $query->whereHas('delivery.employeeSchedules.shiftDetails', function ($q) use ($selectedTimetableId) {
                        $q->where('timetable_id', $selectedTimetableId);
                    });
                })
                ->when($request->filled('delivery_id'), fn($q) => $q->where('delivery_id', $request->delivery_id))

                ->when($request->filled('delivery_phone'), function ($q) use ($request) {
                    $q->whereHas('delivery', function ($query) use ($request) {
                        $query->where('phone_number', 'like', '%' . $request->delivery_phone . '%');
                    });
                })
                ->get();
            // Calculate totals
            $totals = [
                'totalCompletedOrders' => $orders->where('status', 'completed')->count(),
                'TotalCancelledOrders' => $orders->where('status', 'cancelled')->count(),
                'TotalHoldOrders' => $orders->where('status', 'onHold')->count(),
            ];

            $DeliveryReport = $orders->groupBy('delivery_id')->map(function ($orders, $deliveryId) use ($lang) {
                $delivery = $orders->first()->delivery;
                $totalCancelledOrders = $orders->where('status', 'cancelled')->count();
                $totalHoldOrders = $orders->where('status', 'onHold')->count();
                $totalCompletedOrders = $orders->where('status', 'completed')->count();

                $cancellations = $orders->where('status', 'cancelled')->map(function ($order) use ($lang) {
                    // Check if deliveryComplaints exists and get the message
                    $complaintMessage = '-';
                    if ($order->deliveryComplaints) {
                        $complaintMessage = $order->deliveryComplaints->message ?? '-';
                    }

                    return [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number ?? $order->id,
                        'branch_name' => $lang === 'ar'
                            ? ($order->branch->name_ar ?? '-')
                            : ($order->branch->name_en ?? '-'),
                        'cancel_reason' => $complaintMessage,
                    ];
                })->toArray(); // <-- Convert to plain array here
                $onWayTimes = $orders->filter(function ($order) {
                    return $order->tracking->isNotEmpty();
                })->map(function ($order) {
                    $onWayTime = $order->tracking->first()->created_at ?? null;
                    $deliveryTimeMinutes = $order->branch->delivery_time ?? 0;

                    return [
                        'order_number' => $order->order_number ?? $order->id,
                        'estimated_time' => $onWayTime
                            ? \Carbon\Carbon::parse($onWayTime)->addMinutes($deliveryTimeMinutes)->format('H:i')
                            : '-',
                    ];
                })->toArray();
                $onTimeDeliveries = $orders->map(function ($order) {
                    $deliveredTracking = $order->tracking()
                        ->where('order_status', 'delivered')
                        ->orderBy('created_at', 'asc')
                        ->first();

                    return $deliveredTracking ? [
                        'order_number' => $order->order_number ?? $order->id,
                        'delivered_time' => \Carbon\Carbon::parse($deliveredTracking->created_at)->format('H:i'),
                    ] : null;
                })->filter()->values()->toArray();

                $totalDelayMinutes = 0;
                $delayCount = 0;
                $delayedDeliveries = [];
                foreach ($orders as $order) {
                    $onWayTracking = $order->tracking->firstWhere('order_status', 'on_way');
                    $deliveredTracking = $order->tracking()->where('order_status', 'delivered')->orderBy('created_at', 'asc')->first();

                    if ($onWayTracking && $deliveredTracking) {
                        $estimatedTime = Carbon::parse($onWayTracking->created_at)->addMinutes($order->branch->delivery_time ?? 0);
                        $deliveredTime = Carbon::parse($deliveredTracking->created_at);

                        if ($deliveredTime->greaterThan($estimatedTime)) {
                            $delayMinutes = $deliveredTime->diffInMinutes($estimatedTime);
                            $totalDelayMinutes += $delayMinutes;
                            $delayCount++;

                            // ✅ Calculate avg_delivery_time for this order
                            $avgDeliveryTime = $orders->avg(function ($o) {
                                return $o->branch->delivery_time ?? 0;
                            });

                            $delayedDeliveries[] = [
                                'order_number' => $order->order_number ?? $order->id,
                                'delivered_time' => $deliveredTime->format('H:i'),
                                'estimated_time' => $estimatedTime->format('H:i'),
                                'delay_minutes' => $delayMinutes,
                                'avg_delivery_time' => $avgDeliveryTime ? round($avgDeliveryTime, 2) : 0,
                            ];
                        }
                    }
                }


                $avgDelayTime = $delayCount > 0 ? round($totalDelayMinutes / $delayCount, 2) : 0;
                $totalCompleted = $totalCompletedOrders ?: 1; // avoid division by zero
                $delayedPercentage = round(count($delayedDeliveries) / $totalCompleted * 100, 2);

                $avgTimeOfDay = null;
                $estimatedTimes = collect($onWayTimes)->pluck('estimated_time')->filter(fn($time) => $time !== '-');

                if ($estimatedTimes->isNotEmpty()) {
                    $totalMinutes = $estimatedTimes->map(function ($time) {
                        [$hours, $minutes] = explode(':', $time);
                        return (int)$hours * 60 + (int)$minutes;
                    })->sum();

                    $avgMinutes = (int) ($totalMinutes / $estimatedTimes->count());
                    $avgTimeOfDay = \Carbon\Carbon::createFromTime(0, 0)->addMinutes($avgMinutes)->format('H:i');
                }
                $deliveries = Order::with('branch')
                    ->select('delivery_id')->whereNotNull('delivery_id')

                    ->groupBy('delivery_id')
                    ->get()
                    ->map(function ($orderGroup) {
                        // Get all orders for this delivery_id
                        $orders = Order::where('delivery_id', $orderGroup->delivery_id)->with('branch')->get();

                        // Calculate average delivery_time based on branches
                        $avgDeliveryTime = $orders->avg(function ($o) {
                            return $o->branch->delivery_time ?? 0;
                        });

                        // Round it if needed
                        $avgDeliveryTime = $avgDeliveryTime ? round($avgDeliveryTime, 2) : null;

                        // Map each order with its number and shared average delivery time
                        $orderDetails = $orders->map(function ($o) use ($avgDeliveryTime) {
                            return [
                                'order_number' => $o->order_number,
                                'avg_delivery_time' => $avgDeliveryTime
                            ];
                        });

                        return [
                            'delivery_id' => $orderGroup->delivery_id,
                            'orders' => $orderDetails
                        ];
                    });


                return [
                    'delivery_id' => $deliveryId,
                    'time_of_day' => $onWayTimes,
                    'on_time_deliveries' => $onTimeDeliveries,
                    'first_name' => $delivery->first_name ?? '-',
                    'last_name' => $delivery->last_name ?? '-',
                    'full_name' => $delivery ? $delivery->first_name . ' ' . $delivery->last_name : '-',
                    'total_canceled_orders' => $totalCancelledOrders,
                    'total_hold_orders' => $totalHoldOrders,
                    'total_completed_orders' => $totalCompletedOrders,
                    'delayed_delivery_percentage' => $delayedPercentage,
                    'delayed_orders' => $delayedDeliveries,
                    'total_delayed_orders' => count($delayedDeliveries),
                    'avg_delay_time' => $avgDelayTime,
                    'avg_time_of_day' => $avgTimeOfDay,
                    'avg_delivery_time' => count($delayedDeliveries) > 0 ? $deliveries : [],
                    'cancellations' => $cancellations, // Now it's definitely an array
                ];
                // })->values()->toArray();
            })->values();

            $page = $request->page; // current page
            $perPage = $request->per_page; // items per page (default 10)

            if (isset($page) && isset($perPage)) {
                $paginated = new LengthAwarePaginator(
                    $DeliveryReport->forPage($page, $perPage)->values(),
                    $DeliveryReport->count(),
                    $perPage,
                    $page,
                    ['path' => request()->url(), 'query' => request()->query()]
                );

                $meta = [
                    'totalItems'   => $paginated->total(),
                    'itemsPerPage' => $paginated->perPage(),
                    'totalPages'   => $paginated->lastPage(),
                    'currentPage'  => $paginated->currentPage(),
                ];

                return ResponseWithSuccessData($lang, [
                    'DeliveryReport' => $paginated->items(),
                    'meta' => $meta,
                    'timetables' => $timetables,
                    'selectedTimetableId' => $selectedTimetableId,
                    'branches' => Branch::select('id', 'name_ar', 'name_en')->get(),
                    'areas' => Area::select('id', 'name_ar', 'name_en')->get(),
                    'totals' => $totals,
                ], 1);
            } else {
                // Return default meta when no pagination params
                $meta = [
                    'totalItems'   => $DeliveryReport->count(),
                    'itemsPerPage' => 'all',
                    'totalPages'   => 1,
                    'currentPage'  => 1,
                ];

                return ResponseWithSuccessData($lang, [
                    'DeliveryReport' => $DeliveryReport,
                    'meta' => $meta,
                    'timetables' => $timetables,
                    'selectedTimetableId' => $selectedTimetableId,
                    'branches' => Branch::select('id', 'name_ar', 'name_en')->get(),
                    'areas' => Area::select('id', 'name_ar', 'name_en')->get(),
                    'totals' => $totals,
                ], 1);
            }
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 400);
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
                ->whereIn('status', ['cancelled', 'onHold', 'completed'])
                ->whereNotNull('delivery_id')
                ->with([
                    'delivery:id,first_name,last_name',
                    'branch:id,name_ar,name_en,delivery_time',
                    'deliveryComplaints:id,order_id,message',
                    'tracking' => function ($q) {
                        $q->where('order_status', 'on_way')->orderBy('created_at', 'asc');
                    },
                ])
                ->get();

            if ($orders->isEmpty()) {
                return respondError(__('No orders found for this Delivery'), 400);
            }

            $delivery = $orders->first()->delivery;
            $totalCancelledOrders = $orders->where('status', 'cancelled')->count();
            $totalHoldOrders = $orders->where('status', 'onHold')->count();
            $totalCompletedOrders = $orders->where('status', 'completed')->count();

            $cancellations = $orders->where('status', 'cancelled')->map(function ($order) use ($lang) {
                $complaintMessage = $order->deliveryComplaints->message ?? __('report.NotAvailable');

                return [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number ?? $order->id,
                    'branch_name' => $lang === 'ar'
                        ? ($order->branch->name_ar ?? '-')
                        : ($order->branch->name_en ?? '-'),
                    'cancel_reason' => $complaintMessage,
                ];
            })->values();
            $onWayTimes = $orders->filter(fn($o) => $o->tracking->isNotEmpty())->map(function ($order) {
                $onWayTime = $order->tracking->first()->created_at ?? null;
                $deliveryTimeMinutes = $order->branch->delivery_time ?? 0;

                return [
                    'order_number' => $order->order_number ?? $order->id,
                    'estimated_time' => $onWayTime
                        ? \Carbon\Carbon::parse($onWayTime)->addMinutes($deliveryTimeMinutes)->format('H:i')
                        : '-',
                ];
            })->values();

            $onTimeDeliveries = $orders->map(function ($order) {
                $deliveredTracking = $order->tracking()
                    ->where('order_status', 'delivered')
                    ->orderBy('created_at', 'asc')
                    ->first();

                return $deliveredTracking ? [
                    'order_number' => $order->order_number ?? $order->id,
                    'delivered_time' => \Carbon\Carbon::parse($deliveredTracking->created_at)->format('H:i'),
                ] : null;
            })->filter()->values();

            $totalDelayMinutes = 0;
            $delayCount = 0;
            $delayedDeliveries = [];

            foreach ($orders as $order) {
                $onWayTracking = $order->tracking->firstWhere('order_status', 'on_way');
                $deliveredTracking = $order->tracking()->where('order_status', 'delivered')->orderBy('created_at', 'asc')->first();

                if ($onWayTracking && $deliveredTracking) {
                    $estimatedTime = Carbon::parse($onWayTracking->created_at)->addMinutes($order->branch->delivery_time ?? 0);
                    $deliveredTime = Carbon::parse($deliveredTracking->created_at);

                    if ($deliveredTime->greaterThan($estimatedTime)) {
                        $delayMinutes = $deliveredTime->diffInMinutes($estimatedTime);
                        $totalDelayMinutes += $delayMinutes;
                        $delayCount++;

                        $avgDeliveryTime = $orders->avg(fn($o) => $o->branch->delivery_time ?? 0);

                        $delayedDeliveries[] = [
                            'order_number' => $order->order_number ?? $order->id,
                            'delivered_time' => $deliveredTime->format('H:i'),
                            'estimated_time' => $estimatedTime->format('H:i'),
                            'delay_minutes' => $delayMinutes,
                            'avg_delivery_time' => $avgDeliveryTime ? round($avgDeliveryTime, 2) : 0,
                        ];
                    }
                }
            }

            $avgDelayTime = $delayCount > 0 ? round($totalDelayMinutes / $delayCount, 2) : 0;
            $totalCompleted = $totalCompletedOrders ?: 1;
            $delayedPercentage = round(count($delayedDeliveries) / $totalCompleted * 100, 2);

            $estimatedTimes = collect($onWayTimes)->pluck('estimated_time')->filter(fn($time) => $time !== '-');
            $avgTimeOfDay = null;
            if ($estimatedTimes->isNotEmpty()) {
                $totalMinutes = $estimatedTimes->map(function ($time) {
                    [$hours, $minutes] = explode(':', $time);
                    return (int)$hours * 60 + (int)$minutes;
                })->sum();
                $avgMinutes = (int) ($totalMinutes / $estimatedTimes->count());
                $avgTimeOfDay = \Carbon\Carbon::createFromTime(0, 0)->addMinutes($avgMinutes)->format('H:i');
            }

            $avgDeliveryTimePerBranch = $orders->avg(function ($o) {
                return $o->branch->delivery_time ?? 0;
            });

            $response = [
                'delivery_id' => $id,
                'first_name' => $delivery->first_name ?? '-',
                'last_name' => $delivery->last_name ?? '-',
                'total_canceled_orders' => $totalCancelledOrders,
                'total_hold_orders' => $totalHoldOrders,
                'total_completed_orders' => $totalCompletedOrders,
                'cancellations' => $cancellations,
                'time_of_day' => $onWayTimes,
                'on_time_deliveries' => $onTimeDeliveries,
                'delayed_orders' => $delayedDeliveries,
                'avg_delay_time' => $avgDelayTime,
                'delayed_delivery_percentage' => $delayedPercentage,
                'avg_time_of_day' => $avgTimeOfDay,
                'avg_delivery_time' => round($avgDeliveryTimePerBranch, 2),
            ];
            return ResponseWithSuccessData($lang, $response, 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 400);
        }
    }
}
