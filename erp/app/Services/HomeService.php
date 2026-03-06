<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Category;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class HomeService
{
    public function getDashboardData($lang)
    {
        // Counts
        $totalProducts = Product::count();
        $totalOrders   = Order::count();
        $totalDishes   = \App\Models\Dish::count();
        $totalCategory = Category::count();
        $totalBranches = Branch::count();

        // Top branches by orders
        $topBranches = Order::select('branch_id', DB::raw('COUNT(*) as total'))
            ->with(['branch', 'branch.country'])
            ->groupBy('branch_id')
            ->orderByDesc('total')
            ->get();

        $totalOrdersCount = $totalOrders;
        $topBranches = $topBranches->map(function ($branch) use ($totalOrdersCount) {
            $percentage = $totalOrdersCount > 0
                ? round(($branch->total / $totalOrdersCount) * 100, 2)
                : 0;
            return [
                'branch_name'  => $branch->branch->name,
                'country_name' => $branch->branch->country->name,
                'total_orders' => $branch->total,
                'percentage'   => $percentage
            ];
        });

        // Top 5 selling dishes
        $topDishes = OrderDetail::select('dish_id', DB::raw('COUNT(*) as total'))
            ->with('dish')
            ->groupBy('dish_id')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(function ($dish) {
                return [
                    'dish_name'  => $dish->dish->name,
                    'is_active'  => $dish->dish->is_active == 1 ? true : false,
                    'dish_code'  => $dish->dish->code,
                    'image' => asset($dish->dish->image),
                    'total'      => $dish->total
                ];
            });

        // Order summary
        // Order summary
        $orders = Order::with(['orderDetails.dish', 'orderTransactions', 'client', 'address'])
            ->take(10)
            ->get() // <-- This turns it into a Collection
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'client_name' => $order->Client->flag != 'unknown' ? $order->Client->name : ($order->address ? $order->address->user_name : $order->client_name),
                    'address' => $order->address ? $order->address: null,
                    'order_number'    => $order->order_number,
                    'transaction_id'  => $order->orderTransactions->first()->transaction_id ?? 'N/A',
                    'total_price'     => $order->total_price_after_tax,
                    'date'            => $order->date,
                    'type'            => $order->type,
                    'status'          => $order->status,
                ];
            });


        $data = [
            'counts' => [
                'products'  => $totalProducts,
                'orders'    => $totalOrders,
                'dishes'    => $totalDishes,
                'categories' => $totalCategory,
                'branches'  => $totalBranches
            ],
            'top_branches' => $topBranches,
            'top_dishes'   => $topDishes,
            'orders'       => $orders
        ];
        return ResponseWithSuccessData($lang, $data, 1);
    }
}
