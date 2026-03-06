<?php

namespace App\Services\ReportServices;

use App\Models\Order;
use App\Models\Branch;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class DeliveryOrderReportService
{

    public function Index_API(Request $request)
    {
        $selectedTimetableId = $request->input('timetable_id');
        $guard = getAuthenticatedGuard();
        $user  = auth($guard)->user();
        $orders = Order::where('type', 'Delivery')
            ->where('status', 'cancelled')
            ->whereNotNull('delivery_id')
            ->with([
                'delivery',
                'branch',
                'deliveryComplaints',
                'cancellationReasons',
                'cancellationReasons.reasonModel'
            ])

            // ->when($request->filled('branch_id') && ! $user->hasRole('Branch_Manager'), fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($user->hasRole('Branch_Manager'), function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            })
            ->when($request->filled('branch_id') && ! $user->hasRole('Branch_Manager'), function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            })
            ->when($request->filled('from_date'), fn($q) => $q->whereDate('date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn($q) => $q->whereDate('date', '<=', $request->to_date))
            ->when($request->filled('order_number'), fn($q) => $q->where('order_number', 'like', '%' . $request->order_number . '%'))
            ->when($request->filled('delivery_id'), function ($query) use ($request) {
                $query->where('delivery_id', $request->delivery_id);
            })
            ->when($selectedTimetableId, function ($query) use ($selectedTimetableId) {
                $query->whereHas('delivery.employeeSchedules.shiftDetails', function ($q) use ($selectedTimetableId) {
                    $q->where('timetable_id', $selectedTimetableId);
                });
            })
            ->when($request->filled('phone_number'), function ($query) use ($request) {
                $query->whereHas('delivery', function ($q) use ($request) {
                    $q->where('phone_number', 'like', '%' . $request->phone_number . '%');
                });
            });
        $totalOrdersCount = $orders->count();
        $totalComplaintsCount = $orders->clone()->whereHas('deliveryComplaints')->count();

        return [
            'total_orders_count' => $totalOrdersCount,
            'count_complain' => $totalComplaintsCount,
            'orders' => $orders,
        ];
    }

    public function show_API(Request $request, $id)
    {

        $orders = Order::where('type', 'Delivery')
            ->where('status', 'cancelled')
            ->where('delivery_id', $id)
            ->whereNotNull('delivery_id')
            ->with([
                'delivery',
                'branch',
                'deliveryComplaints',
                'cancellationReasons',
                'cancellationReasons.reasonModel'
            ]);
        $totalOrdersCount = $orders->count();
        $totalComplaintsCount = $orders->clone()->whereHas('deliveryComplaints')->count();

        return [
            'total_orders_count' => $totalOrdersCount,
            'count_complain' => $totalComplaintsCount,
            'orders' => $orders,
        ];
    }
    public function index(Request $request)
    {
        try {
            $lang = app()->getLocale();
            $admin = auth('admin')->user();
            if (!$admin) return RespondWithBadRequest($lang, 4);
            App::setLocale($lang);

            $timetables = Timetable::select('id', 'name_ar', 'name_en')->get();
            $selectedTimetableId = $request->input('timetable_id');

            // Get all unique deliveries who have cancelled orders
            $deliveries = Order::where('type', 'Delivery')
                ->where('status', 'cancelled')
                ->whereNotNull('delivery_id')
                ->with('delivery:id,first_name,last_name,phone_number')
                ->get()
                ->pluck('delivery')
                ->unique()
                ->sortBy('first_name');

            // dd($deliveries);

            // Rest of your existing query...
            $orders = Order::where('type', 'Delivery')
                ->where('status', 'cancelled')
                ->whereNotNull('delivery_id')
                ->with([
                    'delivery:id,first_name,last_name,phone_number',
                    'branch:id,name_ar,name_en',
                    'deliveryComplaints:id,order_id,message',
                    'cancellationReasons:id,order_id,reason_id,reason',
                    'cancellationReasons.reasonModel:id,reason_ar,reason_en'
                ])
                ->when($request->filled('branch_id'), fn($q) => $q->where('branch_id', $request->branch_id))
                ->when($request->filled('from_date'), fn($q) => $q->whereDate('date', '>=', $request->from_date))
                ->when($request->filled('to_date'), fn($q) => $q->whereDate('date', '<=', $request->to_date))
                ->when($request->filled('order_number'), fn($q) => $q->where('order_number', 'like', '%' . $request->order_number . '%'))
                ->when($selectedTimetableId, function ($query) use ($selectedTimetableId) {
                    $query->whereHas('delivery.employeeSchedules.shiftDetails', function ($q) use ($selectedTimetableId) {
                        $q->where('timetable_id', $selectedTimetableId);
                    });
                })
                ->when($request->filled('delivery_id'), function ($query) use ($request) {
                    $query->where('delivery_id', $request->delivery_id);
                })
                ->when($request->filled('phone_number'), function ($query) use ($request) {
                    $query->whereHas('delivery', function ($q) use ($request) {
                        $q->where('phone_number', 'like', '%' . $request->phone_number . '%');
                    });
                })
                ->get();

            $DeliveryReport = $orders->groupBy('delivery_id')->map(function ($orders, $deliveryId) use ($lang) {
                $delivery = $orders->first()->delivery;
                $totalOrders = $orders->count();

                $cancellations = $orders->map(function ($order) use ($lang) {
                    // Delivery complaint message
                    $complaintMessage = $order->deliveryComplaints->message ?? '-';

                    // Get cancellation reason data
                    $cancellationReason = $order->cancellationReasons->first();

                    $reasonMessage = $cancellationReason->reason ?? '-'; // From cancellation_reasons.reason
                    $reasonText = '-';

                    if ($cancellationReason && $cancellationReason->reasonModel) {
                        // Get localized reason text from order_cancellation_reasons
                        $reasonText = $lang === 'ar'
                            ? $cancellationReason->reasonModel->reason_ar
                            : $cancellationReason->reasonModel->reason_en;
                    }

                    return [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number ?? $order->id,
                        'branch_name' => $lang === 'ar'
                            ? ($order->branch->name_ar ?? '-')
                            : ($order->branch->name_en ?? '-'),
                        'massage' => $complaintMessage,
                        'reason' => $reasonMessage, // For CancelMassage (from cancellation_reasons.reason)
                        'reason_text' => $reasonText, // For CancelReason (localized from order_cancellation_reasons)
                    ];
                })->toArray();

                return [
                    'delivery_id' => $deliveryId,
                    'first_name' => $delivery->first_name ?? '-',
                    'last_name' => $delivery->last_name ?? '-',
                    'total_orders' => $totalOrders,
                    'cancellations' => $cancellations,
                ];
            })->toArray();
            $totalCancelledOrders = $orders->count();
            $totalComplineOrders = $orders->filter(fn($order) => $order->deliveryComplaints !== null)->count();

            $totals = [
                'TotalCancelledOrders' => $totalCancelledOrders,
                'TotalComplineOrders' => $totalComplineOrders,
            ];

            return ResponseWithSuccessData($lang, [
                'DeliveryReport' => $DeliveryReport,
                'timetables' => $timetables,
                'selectedTimetableId' => $selectedTimetableId,
                'branches' => Branch::select('id', 'name_ar', 'name_en')->get(),
                'deliveries' => $deliveries,
                'totals' => $totals, // ✅ Add this
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

            if (!$admin) {
                return RespondWithBadRequest($lang, 4);
            }

            App::setLocale($lang);

            $orders = Order::where('type', 'Delivery')
                ->where('delivery_id', $id)
                ->where('status', 'cancelled')
                ->whereNotNull('delivery_id')
                ->with([
                    'delivery:id,first_name,last_name',
                    'branch:id,name_ar,name_en',
                    'deliveryComplaints:id,order_id,message',
                    'cancellationReasons:id,order_id,reason_id,reason',
                    'cancellationReasons.reasonModel:id,reason_ar,reason_en'
                ])
                ->get();

            if ($orders->isEmpty()) {
                return respondError(__('No orders found for this Delivery'), 2);
            }

            $delivery = $orders->first()->delivery;

            $cancellations = $orders->map(function ($order) use ($lang) {
                // Note: Changed to cancellationReasons to match index method
                $cancellationReason = $order->cancellationReasons->first();

                return [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number ?? $order->id,
                    'branch_name' => $lang === 'ar'
                        ? ($order->branch->name_ar ?? '-')
                        : ($order->branch->name_en ?? '-'),
                    'massage' => $order->deliveryComplaints->message ?? '-',
                    'reason' => $cancellationReason->reason ?? '-',
                    'reason_text' => $cancellationReason && $cancellationReason->reasonModel
                        ? ($lang === 'ar'
                            ? $cancellationReason->reasonModel->reason_ar
                            : $cancellationReason->reasonModel->reason_en)
                        : '-',
                ];
            });

            $data = [
                'delivery' => [
                    'delivery_id' => $delivery->id ?? '',
                    'first_name' => $delivery->first_name ?? '',
                    'last_name' => $delivery->last_name ?? '',
                    'total_orders' => $orders->count(),
                    'cancellations' => $cancellations->toArray(),
                ]
            ];

            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            logger("Error in show delivery orders: " . $e->getMessage());
            return respondError($e->getMessage(), 2);
        }
    }
    public function getCancelledDeliveryOrders_API(Request $request)
    {
        $lang = app()->getLocale();
        App::setLocale($lang);

        $selectedTimetableId = $request->input('timetable_id');

        $ordersQuery = Order::where('type', 'Delivery')
            ->where('status', 'cancelled')
            ->whereNotNull('delivery_id')
            ->with([
                'delivery:id,first_name,last_name,phone_number',
                'branch:id,name_ar,name_en',
                'deliveryComplaints:id,order_id,message',
                'cancellationReasons:id,order_id,reason_id,reason',
                'cancellationReasons.reasonModel:id,reason_ar,reason_en'
            ])
            ->when($request->filled('branch_id'), fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->filled('from_date'), fn($q) => $q->whereDate('date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn($q) => $q->whereDate('date', '<=', $request->to_date))
            ->when($request->filled('order_number'), fn($q) => $q->where('order_number', 'like', '%' . $request->order_number . '%'))
            ->when($selectedTimetableId, function ($query) use ($selectedTimetableId) {
                $query->whereHas('delivery.employeeSchedules.shiftDetails', function ($q) use ($selectedTimetableId) {
                    $q->where('timetable_id', $selectedTimetableId);
                });
            })
            ->when($request->filled('delivery_id'), function ($query) use ($request) {
                $query->where('delivery_id', $request->delivery_id);
            })
            ->when($request->filled('phone_number'), function ($query) use ($request) {
                $query->whereHas('delivery', function ($q) use ($request) {
                    $q->where('phone_number', 'like', '%' . $request->phone_number . '%');
                });
            });

        return $ordersQuery;
    }

    public function getCancelledDeliveryOrderById_API(Request $request, $id)
    {
        $lang = app()->getLocale();
        App::setLocale($lang);

        $selectedTimetableId = $request->input('timetable_id');

        $ordersQuery = Order::where('type', 'Delivery')
            ->where('status', 'cancelled')
            ->where('delivery_id', $id)
            ->whereNotNull('delivery_id')
            ->with([
                'delivery:id,first_name,last_name,phone_number',
                'branch:id,name_ar,name_en',
                'deliveryComplaints:id,order_id,message',
                'cancellationReasons:id,order_id,reason_id,reason',
                'cancellationReasons.reasonModel:id,reason_ar,reason_en'
            ])
            ->when($request->filled('branch_id'), fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->filled('from_date'), fn($q) => $q->whereDate('date', '>=', $request->from_date))
            ->when($request->filled('to_date'), fn($q) => $q->whereDate('date', '<=', $request->to_date))
            ->when($request->filled('order_number'), fn($q) => $q->where('order_number', 'like', '%' . $request->order_number . '%'))
            ->when($selectedTimetableId, function ($query) use ($selectedTimetableId) {
                $query->whereHas('delivery.employeeSchedules.shiftDetails', function ($q) use ($selectedTimetableId) {
                    $q->where('timetable_id', $selectedTimetableId);
                });
            })
            ->when($request->filled('phone_number'), function ($query) use ($request) {
                $query->whereHas('delivery', function ($q) use ($request) {
                    $q->where('phone_number', 'like', '%' . $request->phone_number . '%');
                });
            });

        return $ordersQuery;
    }
}
