<?php


namespace App\Services\StoreServices;

use App\Models\Size;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SizeService
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    public function index(Request $request, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        // $sizes = Size::all();
        $categories = Category::all();
        $sizes = Size::whereNotNull('category_id')->get();


        if (!$checkToken) {
            $sizes = $sizes->makeVisible(['name_en', 'name_ar']);
        }
        return ResponseWithSuccessData($lang, $sizes, 1 ,$categories);
    }
    public function store(Request $request, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'category_id' => 'required|exists:categories,id', // Validate the category_id
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        $name_ar = $request->name_ar;
        $name_en = $request->name_en;
        $category_id = $request->category_id; // Get the category_id from the request

        if (CheckExistColumnValue('sizes', 'name_ar', $name_ar) || CheckExistColumnValue('sizes', 'name_ar', $name_ar)) {
            return RespondWithBadRequest($lang, 9);
        }
        $created_by =  Auth::guard('admin')->user()->id;

        $size = new Size();
        $size->name_ar = $name_ar;
        $size->name_en =  $name_en;
        $size->category_id = $category_id; // Store the category_id
        $size->created_by =  $created_by;
        $size->save();

        return RespondWithSuccessRequest($lang, 1);
    }
    public function update(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        // Validate the input
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'string',
            'category_id' => 'required|exists:categories,id', // Validate the category_id
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        // Retrieve the size by ID
        $size = Size::find($id);
        if (!$size) {
            return RespondWithBadRequestData($lang, 8);
        }

        // Check if the data has changed
        if ($size->name_ar == $request->name_ar && $size->name_en == $request->name_en && $size->category_id == $request->category_id) {
            return RespondWithBadRequestData($lang, 10);
        }

        if (CheckExistColumnValue('sizes', 'name_ar', $request->name_ar) && CheckExistColumnValue('sizes', 'name_en', $request->name_en) && CheckExistColumnValue('sizes', 'category_id', $request->category_id)) {
            return RespondWithBadRequest($lang, 9);
        }
        $modified_by  =  Auth::guard('admin')->user()->id;

        // Assign the updated values to the size model
        $size->name_ar = $request->name_ar;
        $size->name_en = $request->name_en;
        $size->category_id = $request->category_id; // Update the category_id
        $size->modified_by  = $modified_by ;

        // Update the size in the database
        $size->save();

        return RespondWithSuccessRequest($lang, 1);
    }
    public function delete(Request $request, $id,$checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        // Find the size by ID, or throw a 404 if not found
        $size = Size::find($id);
        if (!$size) {
            return  RespondWithBadRequestData($lang, 8);
        }
        $activeProductSizes = $size->productSizes()->count();
        if ($activeProductSizes > 0) {
            return CustomRespondWithBadRequest(__('size.The size has relations'));
        }

        // Delete the size
        $size->delete();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }

}
