<?php

namespace App\Services\ReportServices;

use App\Models\Dish;
use App\Models\BranchMenu;
use App\Models\OrderAddon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BestSellerDishReportService
{
    public function index(Request $request)
    {
        $lang = app()->getLocale();

        // Retrieve filter parameters
        $from = $request->input('from');
        $to = $request->input('to');
        $branch = $request->input('branch');
        $dishName = $request->input('dish_name');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');

        // Initialize query for dishes
        $dishesQuery = Dish::select(
            'dishes.id',
            'dishes.name_ar',
            'dishes.name_en'
        )
            ->join('order_details', 'order_details.dish_id', '=', 'dishes.id')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->leftJoin('branches', 'branches.id', '=', 'orders.branch_id')
            ->leftJoin('countries', 'countries.id', '=', 'branches.country_id')
            ->whereNull('dishes.deleted_at')

            // Apply date filters
            ->when($from, function ($query) use ($from) {
                $query->whereDate('order_details.created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('order_details.created_at', '<=', $to);
            })
            ->when($branch, function ($query) use ($branch) {
                $query->where('orders.branch_id', $branch);
            })
            // Apply dish name filter
            ->when($dishName, function ($query) use ($dishName, $lang) {
                $nameColumn = $lang == 'en' ? 'dishes.name_en' : 'dishes.name_ar';
                $query->where($nameColumn, 'like', '%' . $dishName . '%');
            });

        // Apply date range if both from and to are present
        if ($from && $to) {
            $dishesQuery->whereBetween('order_details.created_at', [$from, $to]);
        } elseif ($from) {
            $dishesQuery->where('order_details.created_at', '>=', $from);
        } elseif ($to) {
            $dishesQuery->where('order_details.created_at', '<=', $to);
        }

        // Calculate totals
        $dishesQuery->selectRaw('SUM(order_details.quantity) as total_quantity')
            ->selectRaw('SUM(order_details.total) as total_price')
            ->selectRaw('SUM(order_details.quantity * order_details.price_befor_tax) as total_price_before_tax')
            ->selectRaw('SUM(order_details.price_after_tax) as total_price_after_tax')
            ->selectRaw('MAX(order_details.created_at) as latest_order_date')
            ->selectRaw('MAX(countries.currency_symbol) as currency_symbol')
            ->groupBy('dishes.id')
            ->orderByDesc('total_quantity')
            ->orderByDesc('latest_order_date');

        // Apply price filters
        if ($minPrice !== null && $maxPrice !== null) {
            // Both min and max price provided
            $dishesQuery->having('total_price_after_tax', '>=', $minPrice)
                ->having('total_price_after_tax', '<=', $maxPrice);
        } elseif ($minPrice !== null) {
            // Only min price provided
            $dishesQuery->having('total_price_after_tax', '>=', $minPrice);
        } elseif ($maxPrice !== null) {
            // Only max price provided
            $dishesQuery->having('total_price_after_tax', '<=', $maxPrice);
        }

        return $dishesQuery;
    }

    public function show($id)
    {
        $dish = BranchMenu::select(
            'dishes.id',
            'dishes.name_ar',
            'dishes.name_en',
            'dishes.description_ar',
            'dishes.description_en',
            DB::raw('CASE
                WHEN dishes.has_sizes = 1 THEN dish_sizes.price
                ELSE dishes.price
            END as base_price'),
            'dishes.image',
            'dishes.category_id',
            'dishes.cuisine_id',
            'dishes.has_sizes',
            DB::raw('SUM(order_details.quantity) as total_quantity'),
            DB::raw('countries.currency_symbol as currency_symbol'),
            DB::raw('GROUP_CONCAT(DISTINCT branches.name_ar ORDER BY branches.name_ar ASC SEPARATOR ", ") as branch_names'),
            'dish_sizes.price as default_size_price',
            'dish_sizes.id as default_size_id'
        )
            ->leftJoin('dishes', function ($join) {
                $join->on('dishes.id', '=', 'branch_menus.dish_id')
                    ->whereNull('dishes.deleted_at');
            })
            ->leftJoin('branches', function ($join) {
                $join->on('branches.id', '=', 'branch_menus.branch_id')
                    ->whereNull('branches.deleted_at');
            })
            ->leftJoin('order_details', function ($join) {
                $join->on('order_details.dish_id', '=', 'dishes.id')
                    ->whereNull('order_details.deleted_at');
            })
            ->leftJoin('countries', 'countries.id', '=', 'branches.country_id')
            ->leftJoin('dish_sizes', function ($join) {
                $join->on('dish_sizes.dish_id', '=', 'dishes.id')
                    ->where('dish_sizes.default_size', '=', 1)
                    ->whereNull('dish_sizes.deleted_at');
            })
            ->whereNull('branch_menus.deleted_at')
            ->where('dishes.id', $id)
            ->groupBy(
                'dishes.id',
                'dishes.name_ar',
                'dishes.name_en',
                'dishes.description_ar',
                'dishes.description_en',
                'dishes.price',
                'dishes.has_sizes',
                'countries.currency_symbol',
                'dishes.image',
                'dishes.category_id',
                'dishes.cuisine_id',
                'dish_sizes.price',
                'dish_sizes.id'
            )
            ->orderByDesc('total_quantity')
            ->orderBy('dishes.created_at', 'desc')
            ->first();

        $branches = DB::table('order_details')
            ->select(
                'branches.id as branch_id',
                'branches.name_ar',
                'branches.name_en',
                'branches.address_ar',
                'branches.address_en',
                'dish_sizes.size_name_ar',
                'dish_sizes.size_name_en',
                DB::raw('SUM(order_details.quantity) as quantities'),
                DB::raw('SUM(order_details.quantity * order_details.price_befor_tax) as total_price_befor_tax'),
                DB::raw('SUM(order_details.quantity * order_details.price_after_tax) as total_price_after_tax'),
                'order_details.price_befor_tax',
                'order_details.price_after_tax',
                'order_details.tax_value',
                'order_details.note',
                'order_details.quantity',
                'countries.currency_symbol',
                'branches.tax_apply',
                'branches.tax_application',
                'order_details.dish_size_id',
                DB::raw('CASE
                    WHEN dishes.has_sizes = 1 THEN branch_menu_sizes.price
                    ELSE branch_menus.price
                END as dish_price'),
                DB::raw('GROUP_CONCAT(DISTINCT CONCAT(recipes.name_ar, " (", recipes.name_en, ")") ORDER BY recipes.name_ar ASC SEPARATOR ", ") as addon_names')
            )
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->leftJoin('branches', 'branches.id', '=', 'orders.branch_id')
            ->leftJoin('countries', 'countries.id', '=', 'branches.country_id')
            ->leftJoin('order_addons', 'order_addons.order_details_id', '=', 'order_details.id')
            ->leftJoin('dish_addons', 'dish_addons.id', '=', 'order_addons.dish_addon_id')
            ->leftJoin('recipes', 'recipes.id', '=', 'dish_addons.addon_id')
            ->leftJoin('dish_sizes', 'dish_sizes.id', '=', 'order_details.dish_size_id')
            ->leftJoin('dishes', 'dishes.id', '=', 'order_details.dish_id')
            ->leftJoin('branch_menus', function ($join) {
                $join->on('branch_menus.dish_id', '=', 'order_details.dish_id')
                    ->on('branch_menus.branch_id', '=', 'orders.branch_id')
                    ->whereNull('branch_menus.deleted_at');
            })
            ->leftJoin('branch_menu_sizes', function ($join) {
                $join->on('branch_menu_sizes.dish_size_id', '=', 'order_details.dish_size_id')
                    ->on('branch_menu_sizes.branch_id', '=', 'orders.branch_id')
                    ->whereNull('branch_menu_sizes.deleted_at');
            })
            ->where('order_details.dish_id', $id)
            ->groupBy(
                'branches.id',
                'branches.name_ar',
                'branches.name_en',
                'branches.address_ar',
                'branches.address_en',
                'order_details.price_befor_tax',
                'order_details.price_after_tax',
                'order_details.tax_value',
                'order_details.note',
                'orders.id',
                'countries.currency_symbol',
                'branches.tax_apply',
                'branches.tax_application',
                'dish_sizes.size_name_ar',
                'dish_sizes.size_name_en',
                'order_details.dish_size_id',
                'dishes.has_sizes',
                'branch_menu_sizes.price',
                'branch_menus.price'
            )
            ->orderBy('orders.id')
            ->get();

        $addons = OrderAddon::with('Addon')->where('order_id', $id)->get();

        $dish?->load('dishCategory', 'cuisine');

        // Calculate totals
        $totalQuantity = $branches->sum('quantities');
        $totalBeforeTax = $branches->sum('price_befor_tax');
        $totalAfterTax = $branches->sum('price_after_tax');
        $totalTaxValue = $branches->sum('tax_value');
        $currencySymbol = $branches->first()->currency_symbol
            ?? $dish->currency_symbol
            ?? config('app.currency_symbol', '');

        return [
            'dish' => $dish,
            'branches' => $branches,
            'addons' => $addons,
            'totals' => [
                'total_quantity' => $totalQuantity,
                'total_before_tax' => $totalBeforeTax,
                'total_after_tax' => $totalAfterTax,
                'total_tax_value' => $totalTaxValue,
                'currency_symbol' => $currencySymbol
            ]
        ];
    }
    public function print($id)
    {
        // Fetch the dish details and branches as in the `show` method
        $dish = BranchMenu::select(
            'dishes.id',
            'dishes.name_ar',
            'dishes.name_en',
            'dishes.description_ar',
            'dishes.description_en',
            'dishes.price',
            'dishes.image',
            'dishes.category_id',
            'dishes.cuisine_id',
            DB::raw('SUM(order_details.quantity) as total_quantity'),
            DB::raw('countries.currency_symbol as currency_symbol'),
            DB::raw('GROUP_CONCAT(DISTINCT branches.name_ar ORDER BY branches.name_ar ASC SEPARATOR ", ") as branch_names')
        )
            ->leftJoin('dishes', 'dishes.id', '=', 'branch_menus.dish_id')
            ->leftJoin('branches', 'branches.id', '=', 'branch_menus.branch_id')
            ->leftJoin('order_details', 'order_details.dish_id', '=', 'dishes.id')
            ->leftJoin('countries', 'countries.id', '=', 'branches.country_id')
            ->whereNull('dishes.deleted_at')
            ->where('dishes.id', $id)
            ->groupBy(
                'dishes.id',
                'dishes.name_ar',
                'dishes.name_en',
                'dishes.description_ar',
                'dishes.description_en',
                'dishes.price',
                'countries.currency_symbol',
                'dishes.image',
                'dishes.category_id',
                'dishes.cuisine_id'
            )
            ->orderByDesc('total_quantity')
            ->orderBy('dishes.created_at', 'desc')
            ->first();

        // Fetch the branches that have ordered the dish, based on the dish id and order details
        $branches = DB::table('order_details')
            ->select(
                'branches.id as branch_id',
                'branches.name_ar',
                'branches.name_en',
                'branches.address_ar',
                'branches.address_en',
                DB::raw('SUM(order_details.quantity) as quantity'),
                DB::raw('SUM(order_details.quantity * order_details.total) as total_price'), // Assuming you want to calculate total price per branch
                'order_details.price_befor_tax',
                'order_details.price_after_tax',
                'order_details.tax_value',
                'order_details.note'
            )
            ->leftJoin('orders', 'orders.id', '=', 'order_details.order_id')
            ->leftJoin('branches', 'branches.id', '=', 'orders.branch_id')
            ->where('order_details.dish_id', $id)
            ->groupBy(
                'branches.id',
                'branches.name_ar',
                'branches.name_en',
                'branches.address_ar',
                'branches.address_en',
                'order_details.price_befor_tax',
                'order_details.price_after_tax',
                'order_details.tax_value',
                'order_details.note'
            )
            ->orderBy('branches.name_ar', 'asc')
            ->get();

        return view('dashboard.reports.bestDish.print', compact('dish', 'branches'));
    }
}
