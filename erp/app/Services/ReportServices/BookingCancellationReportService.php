<?php


namespace App\Services\ReportServices;

use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderTracking;
use App\Models\OrderTransaction;

class BookingCancellationReportService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function listOrders()
    {
        $query = Order::with(['Table', 'Branch', 'cancellationReasons'])->where('status', 'cancelled')->whereHas('Table')->whereHas('cancellationReasons');
        // $user = auth('admin')->user()??auth('employee')->user();
        // if ($user && ($user->hasRole('Branch Manager'))) {
        //     $branch_id = getBranchManagerID();
        //     if ($branch_id) {
        //         $query->where('branch_id', $branch_id);
        //     }
        // }
        return $query;
    }

    public function orderDetails($id)
    {
        $order = Order::with([
            'orderDetails.dish',
            'address',
            'table',
            'orderAddons',
            'orderTransactions',
            'tracking',
            'client',
            'branch',
            'cancellationReasons' // Load the cancellation reasons
        ])->findOrFail($id);
        $tableName = optional($order->table)->name_ar;
        $order->makeHidden(['table']);
        $order->table_name = $tableName;

        $order['transaction'] = OrderTransaction::first();
//        $order['delivery'] = Order::first();
        return $order;
    }
}
