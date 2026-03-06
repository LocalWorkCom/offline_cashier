<?php

namespace App\Services\ReportServices;

use App\Models\Order;
use App\Models\Branch;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class WaiterTableServiceReportService
{
    public function index(Request $request, $api = 0)
    {
        try {
            $lang = app()->getLocale();
            if ($api == 1) {
                $employee = auth('employee')->user();
                if (!$employee) return RespondWithBadRequest($lang, 4);
            } else {
                $admin = auth('admin')->user();
                if (!$admin) return RespondWithBadRequest($lang, 4);
            }
            App::setLocale($lang);

            // Initialize the timetables variable
            $timetables = Timetable::select('id', 'name_ar', 'name_en')->get();
            $selectedTimetableId = $request->input('timetable_id');
            // dd($selectedTimetableId); // this is BEFORE $selectedTimetableId = $request->timetable_id;

            // Start query for orders
            $orders = Order::where('make_type', 'waiter')
                ->whereNotNull('waiter_id')
                ->with([
                    'waiter:id,id,first_name,last_name,phone_number',
                    'branch:id,name_ar,name_en',
                    'table:id,name_ar,name_en',
                    'orderDetails' => function ($query) {
                        $query->with(['dish' => function ($q) {
                            $q->select('id', 'name_ar', 'name_en');
                        }]);
                    }
                ])
                ->when($request->filled('branch_id'), function ($query) use ($request) {
                    $query->where('branch_id', $request->branch_id);
                })
                ->when($request->filled('waiter_id'), function ($query) use ($request) {
                    $query->where('waiter_id', $request->waiter_id);
                })
                ->when($request->filled('phone_number'), function ($query) use ($request) {
                    $query->whereHas('waiter', function ($q) use ($request) {
                        $q->where('phone_number', 'like', '%' . $request->phone_number . '%');
                    });
                })
                ->when($request->filled('table_id'), function ($query) use ($request) {
                    $query->where('table_id', $request->table_id);
                })
                ->when($request->filled('from_date'), function ($query) use ($request) {
                    $query->whereDate('date', '>=', $request->from_date);
                })
                ->when($request->filled('to_date'), function ($query) use ($request) {
                    $query->whereDate('date', '<=', $request->to_date);
                })
                ->when($selectedTimetableId, function ($query) use ($selectedTimetableId) {
                    $query->whereHas('waiter.employeeSchedules.shiftDetails', function ($query) use ($selectedTimetableId) {
                        $query->where('timetable_id', $selectedTimetableId);
                    });
                });
            //     $q->where('timetable_id', 5);
            // })->get(); //14

            // // dd($waiters->pluck('id'));
            // $orderTest = \App\Models\Order::where('waiter_id', $waiters->first()->id)->get();
            // dd($orderTest->pluck('id'));

            //                     DB::table('orders')
            //                         ->join('employees', 'orders.waiter_id', '=', 'employees.id')
            //                         ->join('employee_schedules', 'employee_schedules.employee_id', '=', 'employees.id')
            //                         ->join('shift_details', 'shift_details.schedule_id', '=', 'employee_schedules.id')
            //                         ->where('shift_details.timetable_id', 5)
            //                         ->select('orders.id')
            //                         ->dd();
            //                 });
            // "select `orders`.`id` from `orders` inner join `employees` on `orders`.`waiter_id` = `employees`.`id` inner join `employee_schedules` on `employee_schedules`.`employee_id` = `employees`.`id` inner join `shift_details` on `shift_details`.`schedule_id` = `employee_schedules`.`id` where `shift_details`.`timetable_id` = ? ◀" // vendor\laravel\framework\src\Illuminate\Database\Query\Builder.php:4128
            // array:1 [▼ // vendor\laravel\framework\src\Illuminate\Database\Query\Builder.php:4128
            //   0 => 5
            // ]
            // dd($selectedTimetableId);

            // Finalize order query and get results
            // dd($orders->get());
            $responseData = [];
            if($api == 1)
            {
                $responseData = paginateOrGetAll($orders, $request, null, null);
                $orders = $responseData['data'];
            }
            else {
                $orders = $orders->get();
            }

            // Calculate totals
            $totals = [
                'total_orders' => $orders->count(),
                'total_price' => $orders->sum('total_price_after_tax'),
                'total_table' => $orders->groupBy('table_id')->count()
            ];

            // Filter by average price after getting all orders
            if ($request->filled('min_avg_price') || $request->filled('max_avg_price')) {
                $orders = $orders->filter(function ($order) use ($request) {
                    $avgPrice = $order->total_price_after_tax;
                    $passMin = $request->filled('min_avg_price') ? $avgPrice >= $request->min_avg_price : true;
                    $passMax = $request->filled('max_avg_price') ? $avgPrice <= $request->max_avg_price : true;
                    return $passMin && $passMax;
                });
            }

            // Group and structure the data per waiter
            $WaiterTableService = $orders->groupBy('waiter_id')->map(function ($orders, $waiterId) {
                $waiter = $orders->first()->waiter;
                $tables = $orders->groupBy('table_id');

                $totalOrders = $orders->count();
                $totalTables = $tables->count();
                $avgPricePerTable = round($orders->avg('total_price_after_tax'), 2);
                $ordersWithSameTable = $tables->filter(function ($groupedOrders) {
                    return $groupedOrders->count() > 1;
                })->sum(function ($groupedOrders) {
                    return $groupedOrders->count();
                });

                return [
                    'waiter_id' => $waiterId,
                    'first_name' => $waiter->first_name ?? '-',
                    'last_name' => $waiter->last_name ?? '-',
                    'total_orders' => $totalOrders,
                    'total_tables' => $totalTables,
                    'avg_price_per_table' => $avgPricePerTable,
                    'orders_with_repeated_tables' => $ordersWithSameTable,
                    'tables' => $tables->map(function ($groupedOrders) {
                        $firstOrder = $groupedOrders->first();
                        $table = $firstOrder->table;

                        // Calculate modifications and cancellations for this table's orders
                        $modifications = [];
                        $cancellations = [];

                        foreach ($groupedOrders as $order) {
                            foreach ($order->orderDetails as $detail) {
                                // Check for modifications (updated_at different from created_at)
                                if ($detail->updated_at->gt($detail->created_at)) {
                                    $dishId = $detail->dish_id;
                                    $dishName = app()->getLocale() === 'ar' ? ($detail->dish->name_ar ?? '-') : ($detail->dish->name_en ?? '-');

                                    if (!isset($modifications[$dishId])) {
                                        $modifications[$dishId] = [
                                            'dish_id' => $dishId,
                                            'dish_name' => $dishName,
                                            'count' => 0
                                        ];
                                    }
                                    $modifications[$dishId]['count']++;
                                }

                                // Check for cancellations (status is 'cancel')
                                if ($detail->status === 'cancel') {
                                    $dishId = $detail->dish_id;
                                    $dishName = app()->getLocale() === 'ar' ? ($detail->dish->name_ar ?? '-') : ($detail->dish->name_en ?? '-');

                                    if (!isset($cancellations[$dishId])) {
                                        $cancellations[$dishId] = [
                                            'dish_id' => $dishId,
                                            'dish_name' => $dishName,
                                            'count' => 0
                                        ];
                                    }
                                    $cancellations[$dishId]['count']++;
                                }
                            }
                        }

                        return [
                            'table_id' => $firstOrder->table_id,
                            'name_ar' => $table->name_ar ?? '-',
                            'name_en' => $table->name_en ?? '-',
                            'order_count' => $groupedOrders->count(),
                            'avg_total_price' => round($groupedOrders->avg('total_price_after_tax'), 2),
                            'modifications' => array_values($modifications),
                            'cancellations' => array_values($cancellations),
                        ];
                    })->values(),
                ];
            })->toArray();

            return ResponseWithSuccessData($lang, [
                'WaiterTableService' => $WaiterTableService,
                'timetables' => $timetables,
                'selectedTimetableId' => $selectedTimetableId,
                'branches' => Branch::select('id', 'name_ar', 'name_en')->get(),
                'selectedBranchId' => $request->branch_id, // The currently selected branch ID
                'totals' => $totals, // Add the totals to the response
                'meta' => $responseData['meta'] ?? null,
            ], 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }
    public function show(Request $request, $id, $api = 0)
    {
        try {
            $lang = app()->getLocale();
            if ($api == 1) {
                $employee = auth('employee')->user();
                if (!$employee) return RespondWithBadRequest($lang, 4);
            } else {
                $admin = auth('admin')->user();
                if (!$admin) return RespondWithBadRequest($lang, 4);
            }

            App::setLocale($lang);

            $orders = Order::where('make_type', 'waiter')
                ->where('waiter_id', $id)
                ->with([
                    'waiter:id,first_name,last_name',
                    'table:id,name_ar,name_en',
                    'orderDetails.dish:id,name_ar,name_en'
                ])
                ->get();

            if ($orders->isEmpty()) {
                return respondError(__('No orders found for this waiter'), 404);
            }

            $waiter = $orders->first()->waiter;
            $tables = $orders->groupBy('table_id');

            $data = [
                'waiter' => [
                    'waiter_id' => $waiter->id ?? '',
                    'first_name' => $waiter->first_name ?? '',
                    'last_name' => $waiter->last_name ?? '',
                    'total_orders' => $orders->count(),
                    'tables' => $tables->map(function ($groupedOrders) use ($lang) {
                        $firstOrder = $groupedOrders->first();
                        $table = $firstOrder->table;

                        $modifications = [];
                        $cancellations = [];

                        foreach ($groupedOrders as $order) {
                            foreach ($order->orderDetails as $detail) {
                                // Modifications
                                if ($detail->updated_at && $detail->updated_at->gt($detail->created_at)) {
                                    $dishId = $detail->dish_id;
                                    $dishName = $lang === 'ar'
                                        ? ($detail->dish->name_ar ?? '-')
                                        : ($detail->dish->name_en ?? '-');

                                    if (!isset($modifications[$dishId])) {
                                        $modifications[$dishId] = [
                                            'dish_id' => $dishId,
                                            'dish_name' => $dishName,
                                            'count' => 0
                                        ];
                                    }
                                    $modifications[$dishId]['count']++;
                                }

                                // Cancellations
                                if ($detail->status === 'cancel') {
                                    $dishId = $detail->dish_id;
                                    $dishName = $lang === 'ar'
                                        ? ($detail->dish->name_ar ?? '-')
                                        : ($detail->dish->name_en ?? '-');

                                    if (!isset($cancellations[$dishId])) {
                                        $cancellations[$dishId] = [
                                            'dish_id' => $dishId,
                                            'dish_name' => $dishName,
                                            'count' => 0
                                        ];
                                    }
                                    $cancellations[$dishId]['count']++;
                                }
                            }
                        }

                        return [
                            'table_id' => $firstOrder->table_id,
                            'name_ar' => $table->name_ar ?? '-',
                            'name_en' => $table->name_en ?? '-',
                            'order_count' => $groupedOrders->count(),
                            'avg_total_price' => round($groupedOrders->avg('total_price_after_tax'), 2),
                            'modifications' => array_values($modifications),
                            'cancellations' => array_values($cancellations),
                        ];
                    })->values()->toArray()
                ]
            ];

            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            logger("Error in show waiter orders: " . $e->getMessage());
            return respondError($e->getMessage(), 2);
        }
    }
}
