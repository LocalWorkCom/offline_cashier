<?php


namespace App\Services\ReportServices;

use App\Models\Order;
use App\Models\WaiterRequest;

class WaiterRequestReportService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }
    public function searchRequests($request)
    {
        $branchId = $request['branch_id'] ?? null;
        $dateFrom = $request['date_from'] ?? null;
        $dateTo = $request['date_to'] ?? null;
        $status = $request['status'] ?? null;
        $type = $request['type'] ?? null;
        $waiterId = $request['waiter_id'] ?? null;
        $orderNumber = $request['order_number'] ?? null;
        $invoiceNum = $request['invoice_number'] ?? null;

        // Get base order query
        $orderQuery = Order::query();
        $branchOrderIds = [];
        $adminUser = auth('admin')->user();
        $employeeUser = auth('employee')->user();

        if ($branchId && $branchId !== 'all') {
            $orderQuery->where('branch_id', $branchId);
        } elseif ($adminUser && $adminUser->hasRole('Branch Manager')) {
            $orderQuery->where('branch_id', getBranchManagerID());
        } elseif ($employeeUser && $employeeUser->hasRole('Branch_Manager')) {
            $orderQuery->where('branch_id', getBranchManagerID());
        }

        if ($orderNumber) {
            $orderQuery->where('order_number', 'like', "%{$orderNumber}%");
        }

        if ($invoiceNum) {
            $orderQuery->where('invoice_number', '=', $invoiceNum); // Exact match
        }

        $branchOrderIds = $orderQuery->pluck('id')->toArray();

        $requests = WaiterRequest::select('id', 'type', 'status', 'order_ids', 'created_at', 'reason')
            ->when(!empty($branchOrderIds), function ($q) use ($branchOrderIds) {
                $q->where(function ($subQ) use ($branchOrderIds) {
                    foreach ($branchOrderIds as $orderId) {
                        $subQ->orWhereJsonContains('order_ids', $orderId);
                    }
                });
            })
            ->when(empty($branchOrderIds), function ($q) {
                $q->whereRaw('1 = 0');
            })
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->when($status !== null && $status !== 'all', fn($q) => $q->where('status', $status))
            ->when($type !== null && $type !== 'all', fn($q) => $q->where('type', $type))
            ->when($waiterId !== null && $waiterId !== 'all', function ($q) use ($waiterId) {
                $orderIds = Order::where('waiter_id', $waiterId)->pluck('id')->toArray();
                if (!empty($orderIds)) {
                    $q->where(function ($subQ) use ($orderIds) {
                        foreach ($orderIds as $orderId) {
                            $subQ->orWhereJsonContains('order_ids', $orderId);
                        }
                    });
                }
            })
            ->get();

        return response()->json($this->prepareRequestsData($requests));
    }

    public function listRequests()
    {
        $branchOrderIds = Order::where('branch_id', getBranchManagerID())->pluck('id')->toArray();

        $requests = WaiterRequest::select('id', 'type', 'status', 'order_ids', 'created_at', 'reason')
            ->when(auth('admin')->user()->hasRole('Branch Manager'), function ($q) use ($branchOrderIds) {
                $q->where(function ($subQ) use ($branchOrderIds) {
                    foreach ($branchOrderIds as $orderId) {
                        $subQ->orWhereJsonContains('order_ids', $orderId);
                    }
                });
            })
            ->get();

        return response()->json($this->prepareRequestsData($requests));
    }

    public function prepareRequestsData($requests)
    {
        $totalReprinted = 0;
        $splitCount = 0;
        $mergeCount = 0;
        $totalPending = 0;
        $totalAccepted = 0;
        $totalRejected = 0;
        $groupedOrders = [];
        foreach ($requests as $request) {
            // حل مشكلة order_ids
            $orderIds = $request->order_ids;
            if (is_string($orderIds)) {
                $orderIds = json_decode($orderIds, true) ?? [];
            }

            if ($request->type == 2) $totalReprinted++;
            elseif ($request->type == 0) $splitCount++;
            elseif ($request->type == 1) $mergeCount++;

            foreach ($orderIds as $orderId) {
                // اتأكد إن المفتاح رقم صحيح
                $id = is_array($orderId) ? ($orderId['id'] ?? null)
                    : (is_object($orderId) ? ($orderId->id ?? null) : $orderId);

                if (!$id) continue; // لو مش لاقي ID يكمل اللي بعده

                if (!isset($groupedOrders[$id])) {
                    $order = Order::find($id);
                    if (!$order) continue;

                    $groupedOrders[$id] = [
                        'id' => $order->id,
                        'branch_name' => optional($order->branch)->name,
                        'branch_id' => $order->branch_id,
                        'waiter_name' => optional($order->waiter)->first_name,
                        'waiter_id' => $order->waiter_id,
                        'cashier_name' => optional($order->cashier)->first_name,
                        'cashier_id' => $order->cashier_id,
                        'order_number' => $order->order_number,
                        'order_type' => $order->type,
                        'order_date' => $order->created_at,
                        'order_status' => $order->status,
                        'table_id' => $order->table_id,
                        'total_amount' => $order->total_price_after_tax,
                        'requests' => []
                    ];
                }

                if ($request->status == 0) $totalPending++;
                elseif ($request->status == 1) $totalAccepted++;
                elseif ($request->status == 2) $totalRejected++;

                $groupedOrders[$id]['requests'][] = [
                    'request_id' => $request->id,
                    'type' => $request->type,
                    'type_name' => $this->getRequestTypeName($request->type),
                    'status' => $request->status,
                    'status_name' => $this->getRequestStatusName($request->status),
                    'created_at' => $request->created_at->toDateTimeString(),
                    'order_ids' => $orderIds,
                    'reason' => $request->reason
                ];
            }
        }
        // foreach ($requests as $request) {
        //     $orderIds = $request->order_ids;

        //     if ($request->type == 2) $totalReprinted++;
        //     elseif ($request->type == 0) $splitCount++;
        //     elseif ($request->type == 1) $mergeCount++;

        //     foreach ($orderIds as $orderId) {
        //         if (!isset($groupedOrders[$orderId])) {
        //             $order = Order::find($orderId);
        //             if (!$order) continue;

        //             $groupedOrders[$orderId] = [
        //                 'id' => $order->id,
        //                 'branch_name' => optional($order->branch)->name,
        //                 'branch_id' => $order->branch_id,
        //                 'waiter_name' => optional($order->waiter)->first_name,
        //                 'waiter_id' => $order->waiter_id,
        //                 'cashier_name' => optional($order->cashier)->first_name,
        //                 'cashier_id' => $order->cashier_id,
        //                 'order_number' => $order->order_number,
        //                 'order_type' => $order->type,
        //                 'order_date' => $order->created_at,
        //                 'order_status' => $order->status,
        //                 'table_id' => $order->table_id,
        //                 'total_amount' => $order->total_price_after_tax,
        //                 'requests' => []
        //             ];
        //         }

        //         if ($request->status == 0) $totalPending++;
        //         elseif ($request->status == 1) $totalAccepted++;
        //         elseif ($request->status == 2) $totalRejected++;

        //         $groupedOrders[$orderId]['requests'][] = [
        //             'request_id' => $request->id,
        //             'type' => $request->type,
        //             'type_name' => $this->getRequestTypeName($request->type),
        //             'status' => $request->status,
        //             'status_name' => $this->getRequestStatusName($request->status),
        //             'created_at' => $request->created_at->toDateTimeString(),
        //             'order_ids' => $orderIds,
        //             'reason' => $request->reason
        //         ];
        //     }
        // }

        $detailedOrders = array_map(function ($order) {
            $order['request_count'] = count($order['requests']);
            return $order;
        }, array_values($groupedOrders));

        return [
            'total_orders_with_requests' => count($groupedOrders),
            'total_pending' => $totalPending,
            'total_accepted' => $totalAccepted,
            'total_rejected' => $totalRejected,
            'total_split' => $splitCount,
            'total_merge' => $mergeCount,
            'total_reprint' => $totalReprinted,
            'orders' => $detailedOrders
        ];
    }

    // Helper methods for request type and status names
    private function getRequestTypeName($type)
    {
        $types = [
            0 => 'Split',
            1 => 'Merge',
            2 => 'Print'
        ];
        return $types[$type] ?? 'Unknown';
    }

    private function getRequestStatusName($status)
    {
        $statuses = [
            0 => 'Pending',
            1 => 'Approved',
            2 => 'Rejected'
        ];
        return $statuses[$status] ?? 'Unknown';
    }

    /**
     * Get request analytics for a specific order
     */
    public function getOrderRequestDetails($orderId)
    {
        $order = Order::with(['waiterRequests' => function ($query) {
            $query->select('id', 'type', 'status', 'order_ids', 'created_at');
        }])
            ->findOrFail($orderId);

        $reprintCount = 0;
        $splitCount = 0;
        $mergeCount = 0;

        foreach ($order->waiterRequests as $request) {
            if ($request->type == 2 && $request->status == 1) {
                $reprintCount++;
            } elseif ($request->type == 0) {
                $splitCount++;
            } elseif ($request->type == 1) {
                $mergeCount++;
            }
        }

        return response()->json([
            'order_id' => $orderId,
            'reprint_count' => $reprintCount,
            'split_count' => $splitCount,
            'merge_count' => $mergeCount,
            'requests' => $order->waiterRequests
        ]);
    }
}
