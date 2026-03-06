<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductColor;
use App\Models\ProductStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductStoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided
        App::setLocale($lang);

        $validator = Validator::make($request->query(), [
            'store_id' => 'required|numeric|exists:stores,id',
            'product_id' => 'required|numeric|exists:products,id',
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $store_id = $request->query('store_id');
        $product_id = $request->query('product_id');

        // Fetch all ProductStore records with their associated products
        $products = ProductStore::with('product')
            ->where('store_id', $store_id)
            ->where('product_id', $product_id)->get();

        // Return the response with the detailed data
        return ResponseWithSuccessData($lang, $products, 1);
    }
}
