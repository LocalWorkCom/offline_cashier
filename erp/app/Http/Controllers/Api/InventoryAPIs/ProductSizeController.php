<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductSizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided
        App::setLocale($lang);

        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }
        $productSizes = ProductSize::all();

        foreach ($productSizes as $productSize) {
            $productSize['size'] = Size::find($productSize->size_id);
            $productSize['product'] = Product::find($productSize->product_id);
        }
        $productSizes->transform(function ($productSize) {
            $productSize->makeHidden(['product_id', 'size_id']);
            return $productSize;
        });

        return ResponseWithSuccessData($lang, $productSizes,  1);
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');  // Set locale from header
        App::setLocale($lang);


        // Check for token validity
        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }

        // Validate the request data
        $validator = Validator::make(
            $request->all(),
            [
                'product_id' => 'required|integer',
                'size_id' => 'required|integer',
                'code_size' => 'required'

            ]
        );

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        if (CheckExistColumnValue('product_sizes', 'product_id', $request->product_id) && CheckExistColumnValue('product_sizes', 'size_id', $request->size_id)) {
            return RespondWithBadRequest($lang, 9);
        }
        // Retrieve data from the request
        $product_id = $request->product_id;
        $size_id = $request->size_id;
        $code_size = $request->code_size;
        $size = Size::find($size_id);
        if (!$size) {
            return  RespondWithBadRequestData($lang, 8);
        }
        $product = Product::find($product_id);
        if (!$product) {
            return  RespondWithBadRequestData($lang, 8);
        }


        // Get the ID of the authenticated user
        $created_by = Auth::guard('api')->user()->id;

        // Create and save the ProductUnit
        $ProductSize = new ProductSize();
        $ProductSize->product_id = $product_id;
        $ProductSize->size_id = $size_id;
        $ProductSize->code_size = $code_size;
        $ProductSize->created_by = $created_by;
        $ProductSize->save();

        // Return a successful response
        return RespondWithSuccessRequest($lang, 1);
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }
        // Validate the input
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'size_id' => 'required|integer',
            'code_size' => 'required'
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }
        if (CheckExistColumnValue('product_sizes', 'product_id', $request->product_id) && CheckExistColumnValue('product_sizes', 'size_id', $request->size_id)) {
            return RespondWithBadRequest($lang, 9);
        }
        // Retrieve the unit by ID, or throw an exception if not found
        $ProductSize = ProductSize::find($id);
        $product_id = $request->product_id;
        $size_id = $request->size_id;
        $code_size = $request->code_size;

        $size = Size::find($size_id);
        if (!$size) {
            return  RespondWithBadRequestData($lang, 8);
        }
        $product = Product::find($product_id);
        if (!$product) {
            return  RespondWithBadRequestData($lang, 8);
        }
        if (
            $ProductSize->product_id == $request->product_id && $ProductSize->size_id == $request->size_id && $ProductSize->code_size == $request->code_size
        ) {
            return  RespondWithBadRequestData($lang, 10);
        }
        $modify_by = Auth::guard('api')->user()->id;
        // Assign the updated values to the unit model
        $ProductSize->product_id = $request->product_id;
        $ProductSize->size_id = $request->size_id;
        $ProductSize->modify_by = $modify_by;
        // Update the unit in the database
        $ProductSize->code_size = $code_size;

        $ProductSize->save();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
    public function delete(Request $request, $id)
    {
        // Fetch the language header for response
        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided
        App::setLocale($lang);

        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }
        // Find the unit by ID, or throw a 404 if not found
        $ProductSize = ProductSize::find($id);
        if (!$ProductSize) {
            return  RespondWithBadRequestData($lang, 8);
        }
        // Delete the unit
        $ProductSize->delete();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
}
