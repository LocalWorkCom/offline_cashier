<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\ProductTransaction;
use App\Services\StoreServices\CategoryInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryInventoryController extends Controller
{
    public function getInventory(Request $request, $categoryId)
    {
        try {
            $lang = $request->header('lang', 'ar');
                $transactions = ProductTransaction::with('products.Category')
                    ->select('product_id','count', 'created_at')
                    ->whereHas('products.Category', function ($query) use ($categoryId) {
                        $query->where('id', $categoryId);
                    })
                    ->orderBy('created_at')
                    ->get();
//                dd($transactions);
            if ($transactions->isEmpty()) {
                return RespondWithBadRequestData($lang, 2);
            }

            $data = CategoryInventoryService::getCategoryInventory($lang, $transactions,$categoryId); ;

            return ResponseWithSuccessData($lang, $data, 1);

        } catch (\Exception $e) {
            Log::error('Error fetching inventory: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
