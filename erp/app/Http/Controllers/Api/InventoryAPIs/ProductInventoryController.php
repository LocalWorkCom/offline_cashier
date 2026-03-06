<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\ProductTransaction;
use App\Services\StoreServices\ProductInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductInventoryController extends Controller
{
    public function getInventory(Request $request, $productId)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $storeId = $request->query('store_id') ?? null;
//            dd($storeId);
            if (is_null($storeId)) {
                $transactions = ProductTransaction::with('products')
                    ->select('product_id','count', 'created_at')
                    ->where('product_id', $productId)
                    ->orderBy('created_at') // tracking the count values along with time
                    ->get();
            }else{
                $transactions = ProductTransaction::with('products')
                    ->select('product_id','count', 'created_at')
                    ->where('product_id', $productId)
                    ->where('store_id', $storeId)
                    ->orderBy('created_at')
                    ->get();
            }

            if ($transactions->isEmpty()) {
                return RespondWithBadRequestData($lang, 2);
            }

            $data = ProductInventoryService::getProductInventory($lang, $transactions,$productId); ;

            return ResponseWithSuccessData($lang, $data, 1);

        } catch (\Exception $e) {
            Log::error('Error fetching inventory: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
