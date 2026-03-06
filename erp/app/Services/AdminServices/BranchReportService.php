<?php

namespace App\Services\AdminServices;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchReportService
{
    public function getBranchesReport($request)
    {
        $filter     = $request->status ?? 'all';
        $from       = $request->from ?? null;
        $to         = $request->to ?? null;
        $address    = $request->address ?? null;
        $branchName = $request->branchName ?? null;

        $branches = Branch::with(['employess', 'country']);

        // Address filter
        if (!empty($address)) {
            $branches->where(function ($query) use ($address) {
                $query->where('address_en', 'LIKE', "%$address%")
                    ->orWhere('address_ar', 'LIKE', "%$address%");
            });
        }

        // Branch name filter
        if (!empty($branchName)) {
            $branches->where(function ($query) use ($branchName) {
                $query->where('name_en', 'LIKE', "%$branchName%")
                    ->orWhere('name_ar', 'LIKE', "%$branchName%");
            });
        }

        // Status filter
        if ($filter !== 'all') {
            if ($filter === 'active') {
                $branches->where('is_active', 1);
            } elseif ($filter === 'inactive') {
                $branches->where('is_active', 0);
            } elseif ($filter === 'delivery') {
                $branches->where('is_delivery', 1);
            } elseif ($filter === 'no-delivery') {
                $branches->where('is_delivery', 0);
            } elseif ($filter === 'noOrders') {
                $branches = Branch::select('branches.*')
                    ->groupBy('branches.id');
            } elseif ($filter === 'mostOrdered') {
                $branches = Branch::select('branches.*')
                    ->groupBy('branches.id');
            } elseif ($filter === 'mostProfit') {
                $branches = Branch::select('branches.*')

                    ->groupBy('branches.id');
            }
        }

        // Date filters
        if ($from) {
            $branches->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $branches->whereDate('created_at', '<=', $to);
        }

        // Total revenue for all branches grouped by currency
        $totalRevenueAllBranches = DB::table('orders')
            ->join('branches', 'orders.branch_id', '=', 'branches.id')
            ->join('countries', 'branches.country_id', '=', 'countries.id')
            ->selectRaw('countries.currency_symbol, SUM(orders.total_price_after_tax) as total_revenue')
            ->groupBy('countries.currency_symbol')
            ->pluck('total_revenue', 'countries.currency_symbol');

            $result = $totalRevenueAllBranches->map(function ($value, $key) {
                return $value . $key;
            })->values()->toArray();

        // All branches list
        $allBranches = Branch::all();
        $fields = [];
        if ($filter == 'mostOrdered') {
            $branches->leftJoin('orders', 'branches.id', '=', 'orders.branch_id')
                    ->select('branches.*', DB::raw('COUNT(orders.id) as order_count'))
                    ->groupBy('branches.id')
                    ->orderByDesc('order_count');
            // $branches->join('orders', 'branches.id', '=', 'orders.branch_id')
            // ->selectRaw('branches.*, COUNT(orders.id) as order_count')->orderByDesc('order_count');
        } else if ($filter == 'mostProfit') {
            $branches->leftJoin('orders', 'branches.id', '=', 'orders.branch_id')
                ->select('branches.*', DB::raw('SUM(orders.total_price_after_tax) as total_revenue'))
                ->groupBy('branches.id')
                ->orderByDesc('total_revenue');
            // $branches->join('orders', 'branches.id', '=', 'orders.branch_id')
            // ->selectRaw('branches.*, SUM(orders.total_price_after_tax) as total_revenue')->orderByDesc('total_revenue');
        } else if ($filter == 'noOrders') {
            $branches->leftJoin('orders', 'branches.id', '=', 'orders.branch_id')
                    ->select('branches.*', DB::raw('COUNT(orders.id) as order_count'))
                    ->groupBy('branches.id')
                    ->havingRaw('COUNT(orders.id) = 0');
            // $branches->Join('orders', 'branches.id', '=', 'orders.branch_id')
            // ->selectRaw('branches.*, COUNT(orders.id) as order_count')
            // ->havingRaw('COUNT(orders.id) = 0');
        }
        $branches = paginateOrGetAll($branches, $request, $fields);
        $responseData['data'] = [
            'branches' => $branches['data'],
            'totalRevenueAllBranches' => $totalRevenueAllBranches,
            'totalRevenue' => $result,
            'allBranches' => $allBranches,
        ];
        $responseData['meta'] = $branches['meta'];
        return $responseData;
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
        $branch = Branch::with(['employess', 'country'])->findOrFail($id);
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

        $data = [
            'branch' => $branch,
            'orders' => $orders,
            'profit' => $profit,
            'totalRevenueAllBranches' => $totalRevenueAllBranches,
        ];
        return $data;
    }
}
