<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\StoreTransaction;
use App\Services\StoreServices\StoreInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoreInventoryController extends Controller
{
    public function getInventory(Request $request, $storeId)
    {
        try {
            $lang = $request->header('lang', 'ar');

            $transactions = StoreTransaction::with('stores')
                ->with('allStoreTransactionDetails.products')
                ->where('store_id', $storeId)
                ->get();
//            dd($transactions);
            if ($transactions->isEmpty()) {
                return RespondWithBadRequestData($lang, 2);
            }

            $data = StoreInventoryService::getStoreInventory($lang, $transactions, $storeId);

            return ResponseWithSuccessData($lang, $data, 1);

        } catch (\Exception $e) {
            Log::error('Error fetching inventory: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
