<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductColor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductColorController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided
        App::setLocale($lang);

        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }
        // Fetch all ProductColor records with their associated products
        $products = ProductColor::with('product')->get();

        // Format each ProductColor and its associated product into an array
        $response = $products->map(function ($productColor) {
            return [
                'color' => $productColor->toArray(), // Full details of the color
                'product' => $productColor->product ? $productColor->product->toArray() : null, // Full details of the associated product, if exists
            ];
        });

        // Return the response with the detailed data
        return ResponseWithSuccessData($lang, $response, code: 1);
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
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'color_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        // Retrieve data from the request
        $product_id = $request->product_id;
        $color_id = $request->color_id;
        $size = Color::find($color_id);
        if (!$size) {
            return  RespondWithBadRequestData($lang, 8);
        }
        $product = Product::find($product_id);
        if (!$product) {
            return  RespondWithBadRequestData($lang, 8);
        }
        if (CheckExistColumnValue('product_colors', 'product_id', $request->product_id) && CheckExistColumnValue('product_colors', 'color_id', $request->color_id)) {
            return RespondWithBadRequest($lang, 9);
        }
        // Get the ID of the authenticated user
        $created_by = Auth::guard('api')->user()->id;

        // Create and save the ProductUnit
        $productColor = new ProductColor();
        $productColor->product_id = $product_id;
        $productColor->color_id = $color_id;
        $productColor->created_by = $created_by;
        $productColor->save();

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
            'color_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }
        $product_id = $request->product_id;
        $color_id = $request->color_id;
        $color = Color::find($color_id);
        if (!$color) {
            return  RespondWithBadRequestData($lang, 8);
        }
        $product = Product::find($product_id);
        if (!$product) {
            return  RespondWithBadRequestData($lang, 8);
        }
        if (CheckExistColumnValue('product_colors', 'product_id', $request->product_id) && CheckExistColumnValue('product_colors', 'color_id', $request->color_id)) {
            return RespondWithBadRequest($lang, 9);
        }
        $productColor = ProductColor::find($id);
        if (
            $productColor->product_id == $request->product_id && $productColor->color_id == $request->color_id
        ) {
            return  RespondWithBadRequestData($lang, 10);
        }

        // Retrieve the unit by ID, or throw an exception if not found

        $modify_by = Auth::guard('api')->user()->id;
        // Assign the updated values to the unit model
        $productColor->product_id = $request->product_id;
        $productColor->color_id = $request->color_id;
        $productColor->modify_by = $modify_by;
        // Update the unit in the database
        $productColor->save();

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
        $productColor = ProductColor::find($id);
        if (!$productColor) {
            return  RespondWithBadRequestData($lang, 8);
        }

        // Delete the unit
        $productColor->delete();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
}
