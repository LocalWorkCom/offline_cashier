<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\ProductTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $today = date('Y-m-d');
            $stores = ProductTransaction::query()->whereDate('expired_date', '>=', $today);
            $stores = $stores->select('product_id', DB::raw('sum(count) as total_count'));
            $stores = $stores->with(['products', 'products.unites', 'products.sizes', 'products.colors'])->groupBy('product_id')->get();

            return ResponseWithSuccessData($lang, $stores, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $today = date('Y-m-d');
            $stores = ProductTransaction::query()->where('product_id', $id)->whereDate('expired_date', '>=', $today);
            $stores = $stores->select('product_id', DB::raw('sum(count) as total_count'));
            $stores = $stores->with(['products', 'products.unites', 'products.sizes', 'products.colors'])->groupBy('product_id')->first();

            return ResponseWithSuccessData($lang, $stores, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
