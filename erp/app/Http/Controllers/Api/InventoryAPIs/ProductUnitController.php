<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided
        App::setLocale($lang);

        $productUnits = ProductUnit::all();

        $productUnits = $productUnits->map(function ($data) {

            $data['unit'] = Unit::find($data['unit_id']);;
            $data['product'] = Product::find($data['product_id']);

            unset($data['product_id'], $data['unit_id']);

            return $data;
        });

        // removeColumns($productUnits, ['product_id', 'unit_id']);
        return ResponseWithSuccessData($lang, $productUnits,  1);
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');  // Set locale from header
        App::setLocale($lang);


        // Validate the request data
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'unit_id' => 'required|integer',
            'factor' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        // Retrieve data from the request
        $product_id = $request->product_id;
        $unit_id = $request->unit_id;
        $factor = $request->factor;
        $unit = Unit::find($unit_id);
        if (!$unit) {
            return  RespondWithBadRequestData($lang, 8);
        }
        $product = Product::find($product_id);
        if (!$product) {
            return  RespondWithBadRequestData($lang, 8);
        }
        // Get the ID of the authenticated user
        $created_by = Auth::guard('api')->user()->id;

        // Create and save the ProductUnit
        $productUnit = new ProductUnit();
        $productUnit->product_id = $product_id;
        $productUnit->unit_id = $unit_id;
        $productUnit->factor = $factor;
        $productUnit->created_by = $created_by;
        $productUnit->save();

        // Return a successful response
        return RespondWithSuccessRequest($lang, 1);
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        // Validate the input
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'unit_id' => 'required|integer',
            'factor' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }
        $product_id = $request->product_id;
        $unit_id = $request->unit_id;
        $unit = Unit::find($unit_id);
        if (!$unit) {
            return  RespondWithBadRequestData($lang, 8);
        }
        $product = Product::find($product_id);
        if (!$product) {
            return  RespondWithBadRequestData($lang, 8);
        }
        if (CheckExistColumnValue('product_units', 'product_id', $request->product_id) && CheckExistColumnValue('product_units', 'unit_id', $request->unit_id)) {
            return RespondWithBadRequest($lang, 9);
        }
        // Retrieve the unit by ID, or throw an exception if not found
        $productUnit = ProductUnit::find($id);
        if (
            $productUnit->product_id == $request->product_id && $productUnit->unit_id == $request->unit_id && $productUnit->factor == $request->factor
        ) {
            return  RespondWithBadRequestData($lang, 10);
        }
        $factor = $request->factor;

        $modify_by = Auth::guard('api')->user()->id;
        // Assign the updated values to the unit model
        $productUnit->product_id = $request->product_id;
        $productUnit->unit_id = $request->unit_id;
        $productUnit->factor = $factor;
        $productUnit->modify_by = $modify_by;
        // Update the unit in the database
        $productUnit->save();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
    public function delete(Request $request, $id)
    {
        // Fetch the language header for response
        $lang = $request->header('lang', 'ar');  
        App::setLocale($lang);

        // Find the unit by ID, or throw a 404 if not found
        $productUnit = ProductUnit::find($id);
        if (!$productUnit) {
            return  RespondWithBadRequestData($lang, 8);
        }

        // Delete the unit
        $productUnit->delete();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
}
