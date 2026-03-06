<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderProduct;
use App\Models\OrderRefund;
use App\Models\OrderTracking;
use App\Models\OrderTransaction;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


class OrderReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    public function OrderReport(Request $request)
    {

        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided
        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }
        DB::statement('SET SESSION sql_mode = (SELECT REPLACE(@@sql_mode, "ONLY_FULL_GROUP_BY", ""));');


        $orders = Order:: // Load only necessary fields
            select(
                'orders.invoice_number as inv_num',
                'orders.type as order_type',
                'orders.status as order_status',
                'orders.total_price_befor_tax as price_before_tax',
                'orders.total_price_after_tax as price_after_tax',
                'orders.date as order_date',
                'orders.tax_value as tax_value',
                'orders.order_number as order_number',
                'users.name as client_name',
                'branches.name_ar as branch_name',
                'order_transactions.payment_status as payment_status',
                'order_transactions.payment_method  as payment_method',
                DB::raw('SUM(orders.total_price_after_tax) as total') // Aggregate with DB::raw

            )
            ->leftJoin('users', 'orders.client_id', '=', 'users.id')
            ->leftJoin('branches', 'orders.branch_id', '=', 'branches.id')
            ->leftJoin('order_transactions', 'orders.id', '=', 'order_transactions.order_id')
            ->groupBy(
                'orders.id'

            );
        if ($request->start_date) {
            $orders->where('date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $orders->where('date', '<=', $request->end_date);
        }
        if ($request->client_id) {
            $orders->where('client_id', $request->client_id);
        }
        if ($request->branch_id) {
            $orders->where('branch_id', $request->branch_id);
        }
        if ($request->user_id) {
            $orders->where('created_by', $request->user_id);
        }

        $orders = $orders->get();


        return ResponseWithSuccessData($lang, $orders, 1);
    }
    public function OrderReportDetails(Request $request)
    {

        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided
        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }
        $order = Order::where('orders.id', $request->order_id)
            ->select(
                'orders.invoice_number as inv_num',
                'orders.type as order_type',
                'orders.status as order_status',
                'orders.total_price_befor_tax as price_before_tax',
                'orders.total_price_after_tax as price_after_tax',
                'orders.date as order_date',
                'orders.tax_value as tax_value',
                'orders.order_number as order_number',
                'users.name as client_name',
                'branches.name_ar as branch_name',
                'order_transactions.payment_status as payment_status',
                'order_transactions.payment_method  as payment_method'
            )
            ->leftJoin('users', 'orders.client_id', '=', 'users.id')
            ->leftJoin('branches', 'orders.branch_id', '=', 'branches.id')
            ->leftJoin('order_transactions', 'orders.id', '=', 'order_transactions.order_id')
            ->first();

        $order['details'] = OrderDetail::leftJoin('dishes', 'dishes.id', 'order_details.dish_id')->where('order_id', $request->order_id)
            ->select('order_details.quantity as quantity', 'price_after_tax as total', 'dishes.name_ar', 'order_details.status as status')
            ->get();

        $order['products'] = OrderProduct::leftJoin('products', 'products.id', 'order_products.product_id')->leftJoin('units', 'units.id', 'order_products.unit_id')->where('order_id', $request->order_id)
            ->select('order_products.quantity as quantity', 'order_products.price', 'products.name_ar as name')
            ->get();


        $order['tracking'] = OrderTracking::where('order_id', $request->order_id)->pluck('order_status')->toArray();

        return ResponseWithSuccessData($lang, $order, 1);
    }
    public function OrderRefundReport(Request $request)
    {

        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided
        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }
        DB::statement('SET SESSION sql_mode = (SELECT REPLACE(@@sql_mode, "ONLY_FULL_GROUP_BY", ""));');

        $orders = OrderRefund::select(
            'orders.type as order_type',
            'orders.status as order_status',
            'orders.total_price_befor_tax as price_before_tax',
            'orders.total_price_after_tax as price_after_tax',
            'orders.date as order_date',
            // 'dishes.name_ar',
            'orders.tax_value as tax_value',
            // 'orders.order_number as order_number',
            'users.name as client_name',
            'branches.name_ar as branch_name',
            'order_refunds.invoice_number',
            'order_refunds.date',
            'order_transactions.payment_status as payment_status',
            'order_transactions.payment_method as payment_method',
            DB::raw('SUM(order_refunds.price) as total')
        )
            ->leftJoin('orders', 'orders.id', 'order_details.order_id')
            ->leftJoin('order_transactions', 'orders.id', '=', 'order_transactions.order_id')
            ->leftJoin('users', 'orders.client_id', '=', 'users.id')
            ->leftJoin('branches', 'orders.branch_id', '=', 'branches.id')
            ->groupBy(
                'order_refunds.id'
            );


        if ($request->start_date) {
            $orders->where('date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $orders->where('date', '<=', $request->end_date);
        }
        if ($request->client_id) {
            $orders->where('client_id', $request->client_id);
        }
        if ($request->branch_id) {
            $orders->where('branch_id', $request->branch_id);
        }
        if ($request->user_id) {
            $orders->where('created_by', $request->user_id);
        }

        $orders = $orders->get();

        foreach ($orders as $order) {
            if ($order->item_type == 'product') {
                $order['item_name'] = Product::find($order->item_id)->name_ar;
            } else if ($order->item_type == 'dish') {
                $order['item_name'] = Dish::find($order->item_id)->name_ar;
            } else {
                $order['item_name'] = Recipe::find($order->item_id)->name_ar;
            }
        }

        return ResponseWithSuccessData($lang, $orders, 1);
    }
    public function OrderRefundReportDetails(Request $request)
    {

        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided
        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }
        $orders = OrderRefund::where('id', $request->order_refund_id)
            ->select(
                'orders.invoice_number as inv_num',
                'orders.type as order_type',
                'orders.status as order_status',
                'orders.total_price_befor_tax as price_before_tax',
                'orders.total_price_after_tax as price_after_tax',
                'orders.date as order_date',
                'orders.tax_value as tax_value',
                'orders.order_number as order_number',
                'users.name as client_name',
                'branches.name_ar as branch_name',
                'order_transactions.payment_status as payment_status',
                'order_transactions.payment_method  as payment_method'
            )
            ->leftJoin('order_details', 'order_details.id', 'order_refunds.order_detail_id')
            ->leftJoin('orders', 'orders.id', 'order_details.order_id')
            ->leftJoin('order_transactions', 'orders.id', '=', 'order_transactions.order_id')
            ->leftJoin('users', 'orders.client_id', '=', 'users.id')
            ->leftJoin('branches', 'orders.branch_id', '=', 'branches.id')
            ->first();


        return ResponseWithSuccessData($lang, $orders, 1);
    }
}
