<?php

namespace App\Services\KitchenServices;

use App\Models\KitchenLog;
use App\Models\ShiftDetail;
use Illuminate\Http\Request;

class KitchenStaffService
{
    private $lang;

    public function __construct(Request $request)
    {
        $this->lang = $request->header('lang', 'ar');
    }

    public function listRequests($request, $id = false)
    {
        if($id)
        {
            $query = KitchenLog::where('order_id', $id);
        }
        else
        {
            $query = KitchenLog::query();
        }

        // Role-based branch filter
        $user = auth('admin')->user();
        $employee = auth('employee')->user();
        $created = $user ? $user : $employee;
        if ($user && $user->hasRole('Branch Manager')) {
            $branchId = getBranchManagerID();
            $query->where('branch_id', $branchId);
        } else {
            if ($created->branch_id) {
                $query->where('branch_id', $created->branch_id);
            }
        }

        // Filters
        if ($request->order_number) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->order_number . '%');
            });
        }

        if ($request->branch_id && $request->branch_id !== 'all') {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->employee_id && $request->employee_id !== 'all') {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->dish_id && $request->dish_id !== 'all') {
            $query->where('dish_id', $request->dish_id);
        }

        if ($request->category_id && $request->category_id !== 'all') {
            $query->whereHas('dish', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->date_from) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            });
        }

        if ($request->date_to) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            });
        }

        if ($employee) {
            $query->select('order_id', 'dish_id', 'branch_id', 'employee_id')
                ->groupBy('order_id', 'dish_id', 'branch_id', 'employee_id');
            $orders = paginateOrGetAll($query, $request, []);
            $meta = $orders['meta'];
            $orders = $orders['data'];
        } else {
            $orders = $query->select('order_id', 'dish_id', 'branch_id', 'employee_id')
                ->groupBy('order_id', 'dish_id', 'branch_id', 'employee_id')
                ->get();
            $meta = null;
        }
        if (count($orders) == 0) {
            return [
                'success' => true,
                'totals' => [
                    'totalOrders' => 0,
                    'totalOnTime' => 0,
                    'totalFaster' => 0,
                    'totalDelayed' => 0,
                ],
                'data' => [],
                'meta' => $meta,
            ];
        }
        $orders->load([
            'order:id,order_number,created_at',
            'dish:id,name_ar,time',
            'branch:id,name_ar',
            'employee:id,first_name',
            'employee.employeeSchedules.shift:id',
        ]);

        $report = [];
        $totalOnTime = $totalFaster = $totalDelayed = $totalOrders = 0;

        foreach ($orders as $log) {
            $totalOrders++;

            if($id)
            {
                $dishLogs = KitchenLog::where('order_id', $id)->where('order_id', $log->order_id)
                ->where('dish_id', $log->dish_id)
                ->orderBy('created_at')
                ->get();
            }
            else{

                $dishLogs = KitchenLog::where('order_id', $log->order_id)
                    ->where('dish_id', $log->dish_id)
                    ->orderBy('created_at')
                    ->get();
            }

            $inProgress = $dishLogs->firstWhere('status', 'in_progress');
            $completed = $dishLogs->firstWhere('status', 'completed');

            $actualTime = $expectedTime = null;
            $expectedTime = optional($log->dish)->time;

            if ($inProgress && $completed) {
                $actualTime = $inProgress->created_at->diffInMinutes($completed->created_at);
            }

            $status = null;
            if (!is_null($actualTime) && !is_null($expectedTime)) {
                if ($actualTime == $expectedTime) {
                    $status = 'OnTime';
                    $totalOnTime++;
                } elseif ($actualTime < $expectedTime) {
                    $status = 'Faster';
                    $totalFaster++;
                } else {
                    $status = 'Delayed';
                    $totalDelayed++;
                }
            }

            if ($request->status && $request->status !== 'all') {
                if ($status !== $request->status) {
                    continue;
                }
            }

            $orderDate = optional($log->order)->created_at;
            $shiftTime = null;

            if ($log->employee && $orderDate) {
                $schedule = $log->employee->scheduleForDate($orderDate);
                if ($schedule) {
                    $shiftId = $schedule->shift_id;
                    $dayIndex = $orderDate->dayOfWeek;

                    $shiftDetail = ShiftDetail::where('shift_id', $shiftId)
                        ->where('day_index', $dayIndex)
                        ->first();

                    if ($shiftDetail && $shiftDetail->timetable) {
                        $shiftTime = [
                            'shift_id' => $shiftDetail->shift_id,
                            'timetable_id' => $shiftDetail->timetable_id,
                            'shift_name' => $shiftDetail->shift->name_ar,
                            'start_time' => $shiftDetail->timetable->on_duty_time,
                            'end_time' => $shiftDetail->timetable->off_duty_time,
                        ];
                    }
                }
            }

            if ($request->shift_id && $request->shift_id !== 'all') {
                if (!$shiftTime || $shiftTime['shift_id'] != $request->shift_id) {
                    continue;
                }
            }

            $report[] = [
                'order_id' => $log->order_id,
                'dish_id' => $log->dish_id,
                'order_details_id' => $log->order_details_id,
                'branch_id' => $log->branch_id,
                'employee_id' => $log->employee_id,
                'order_number' => optional($log->order)->order_number,
                'branch_name' => optional($log->branch)->name_ar,
                'dish_name' => optional($log->dish)->name_ar,
                'expected_time' => $expectedTime,
                'actual_time' => $actualTime,
                'status' => $status,
                'employee_name' => optional($log->employee)->first_name,
                'shift_time' => $shiftTime,
            ];
        }

        return [
            'success' => true,
            'totals' => [
                'totalOrders' => $totalOrders,
                'totalOnTime' => $totalOnTime,
                'totalFaster' => $totalFaster,
                'totalDelayed' => $totalDelayed,
            ],
            'data' => $report,
            'meta' => $meta,
        ];
    }



    public function searchRequests($search)
    {
        $query = KitchenLog::with(['user', 'branch'])
            ->orderBy('created_at', 'desc');

        return $query;
    }
}
