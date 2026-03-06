<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Dish;
use Illuminate\Http\Request;

class BranchesReportsController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('status', 'all');
        $from = $request->get('from');
        $to = $request->get('to');
        $address = $request->get('address'); // Get address filter from request
        $branchName = $request->get('branchName'); // Get branchName filter from request

        $branches = Branch::with(['employess', 'country']);

        // Apply address filter
        if (!empty($address)) {
            $branches->where(function ($query) use ($address) {
                $query->where('address_en', 'LIKE', "%$address%")
                    ->orWhere('address_ar', 'LIKE', "%$address%");
            });
        }

        // Apply branchName filter
        if (!empty($branchName)) {
            $branches->where(function ($query) use ($branchName) {
                $query->where('name_en', 'LIKE', "%$branchName%")
                    ->orWhere('name_ar', 'LIKE', "%$branchName%");
            });
        }


        // Apply status filter
        if ($filter !== 'all') {
            if ($filter === 'active') {
                $branches->where('is_active', 1);
            } elseif ($filter === 'inactive') {
                $branches->where('is_active', 0);
            } elseif ($filter === 'delivery') {
                $branches->where('is_delivery', 1);
            }elseif ($filter === 'no-delivery') {
                $branches->where('is_delivery', 0);
            }elseif ($filter === 'noOrders') {
                $branches = Branch::select('branches.*')
                    ->leftJoin('orders', 'branches.id', '=', 'orders.branch_id') // Use LEFT JOIN
                    ->selectRaw('branches.*, COUNT(orders.id) as order_count')
                    ->groupBy('branches.id')
                    ->havingRaw('COUNT(orders.id) = 0'); // Filter branches with no orders
            }elseif ($filter === 'mostOrdered') {
                $branches = Branch::select('branches.*')
                    ->join('orders', 'branches.id', '=', 'orders.branch_id')
                    ->selectRaw('branches.*, COUNT(orders.id) as order_count')
                    ->groupBy('branches.id')
                    ->orderByDesc('order_count');
            } elseif ($filter === 'mostProfit') {
                $branches = Branch::select('branches.*')
                    ->join('orders', 'branches.id', '=', 'orders.branch_id')
                    ->selectRaw('branches.*, SUM(orders.total_price_after_tax) as total_revenue')
                    ->groupBy('branches.id')
                    ->orderByDesc('total_revenue');
            }
        }

        // Apply date filter
        if ($from) {
            $branches->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $branches->whereDate('created_at', '<=', $to);
        }
//        dd($branches->get());

        $totalRevenueAllBranches = \DB::table('orders')
            ->join('branches', 'orders.branch_id', '=', 'branches.id')
            ->join('countries', 'branches.country_id', '=', 'countries.id')
            ->selectRaw('countries.currency_symbol, SUM(orders.total_price_after_tax) as total_revenue')
            ->groupBy('countries.currency_symbol')
            ->pluck('total_revenue', 'countries.currency_symbol');
//        dd($totalRevenueAllBranches);

        $allbranches = Branch::all();


        return view('dashboard.reports.branches.list', [
            'branches' => $branches->get(),
            'totalRevenueAllBranches' => $totalRevenueAllBranches,
            'allbranches' => $allbranches,
        ]);
    }


    public function show($id)
    {
        $branch = Branch::with(['employess','country'])->findOrFail($id);
//        dd($branch->country->currency_symbol);
        $profit = Branch::select('branches.*')
            ->join('orders', 'branches.id', '=', 'orders.branch_id')
            ->selectRaw('branches.*, SUM(orders.total_price_after_tax) as total_revenue')
            ->where('branches.id', $id)
            ->groupBy('branches.id')
            ->orderByDesc('total_revenue')
            ->first();
        $orders = Branch::select('branches.*')
            ->join('orders', 'branches.id', '=', 'orders.branch_id')
            ->selectRaw('branches.*, COUNT(orders.id) as order_count, SUM(orders.total_price_after_tax) as total_revenue')
            ->where('branches.id', $id)
            ->groupBy('branches.id')
            ->orderByDesc('order_count')
            ->first();

        $totalRevenueAllBranches = \DB::table('orders')
            ->join('branches', 'orders.branch_id', '=', 'branches.id')
            ->join('countries', 'branches.country_id', '=', 'countries.id')
            ->selectRaw('countries.currency_symbol, SUM(orders.total_price_after_tax) as total_revenue')
            ->groupBy('countries.currency_symbol')
            ->pluck('total_revenue', 'countries.currency_symbol');

        return view('dashboard.reports.branches.show', compact('branch','orders','profit','totalRevenueAllBranches'));
    }
}
