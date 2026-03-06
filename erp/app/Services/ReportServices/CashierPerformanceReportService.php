<?php

namespace App\Services\ReportServices;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CashierPerformanceReportService
{
    /**
     * List cashier performance with filters
     */
    public function listCashiers(Request $request)
    {
        $cashierId = $request->input('cashier_id');
        $phoneNumber = $request->input('phone_number');
        $from = $request->input('from');
        $to = $request->input('to');
        $branchId = $request->input('branch_id');

        $query = Employee::where('flag', 'cashier')
                ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->with(['branch.country'])
            ->with(['orders' => function ($query) use ($from, $to) {
                $query->where('status', 'completed');
                if ($from) $query->whereDate('created_at', '>=', $from);
                if ($to) $query->whereDate('created_at', '<=', $to);
            }]);

        // count orders
        $query->withCount(['orders as order_count' => function ($q) use ($from, $to) {
            if ($from) $q->whereDate('created_at', '>=', $from);
            if ($to) $q->whereDate('created_at', '<=', $to);
            $q->where('status', 'completed');
        }]);

        // sum order totals
        $query->withSum(['orders as total_order_price' => function ($q) use ($from, $to) {
            if ($from) $q->whereDate('created_at', '>=', $from);
            if ($to) $q->whereDate('created_at', '<=', $to);
            $q->where('status', 'completed');
        }], 'total_price_after_tax');

        // filter by cashier id
        if ($cashierId && $cashierId !== 'all') {
            $query->where('id', $cashierId);
        }
        if ($phoneNumber) {
            $query->where('phone_number', $phoneNumber);
        }

        return [
            'cashiers' => $query,
            'allCashiers' => Employee::where('flag', 'cashier'),
        ];
    }

    /**
     * Get single cashier performance
     */
    public function showCashier($id)
    {
        $cashier = Employee::where('flag', 'cashier')
            ->where('id', $id)
            ->with(['branch.country'])
            ->with(['orders' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->withCount(['orders as order_count' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->withSum(['orders as total_order_price' => function ($q) {
                $q->where('status', 'completed');
            }], 'total_price_after_tax')
            ->first();

        if (!$cashier) {
            return false;
        }

        return $cashier;
    }
}
