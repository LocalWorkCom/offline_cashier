<?php

namespace App\Services\StoreServices;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class CategoryService
{

    public function index(Request $request, $checkToken)
    {

        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        $categories = Category::all();


        if (!$checkToken) {
            $categories = $categories->makeVisible(['name_en', 'name_ar', 'image', 'description_ar', 'description_en']);
        }

        return ResponseWithSuccessData($lang, $categories, 1);
    }
    public function store(Request $request, $checkToken)
    {
        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image' => 'required|mimes:jpeg,png,jpg,gif,svg',  // Restrict image extensions
            'is_freeze' => 'required|boolean',
            'parent_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }
        if (CheckExistColumnValue('categories', 'name_ar', $request->name_ar) || CheckExistColumnValue('categories', 'name_en', $request->name_en)) {
            return RespondWithBadRequest($lang, 9);
        }

        $GetLastID = GetLastID('categories');
        // dd($GetLastID);

        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided

        $name_ar = $request->name_ar;
        $name_en = $request->name_en;
        $description_ar = $request->description_ar;
        $description_en = $request->description_en;
        $image = $request->file('image');  // Handle file upload if necessary
        $code = GenerateCode('categories', ($GetLastID == 1) ? 0 : $GetLastID);
        $is_freeze = $request->is_freeze;
        $parent_id = isset($request->parent_id) && !empty($request->parent_id) ? $request->parent_id : null;
        if ($parent_id) {
            $category = Category::find($parent_id);
            if (!$category) {
                $category_valid['parent_id'] = ['الفئة غير موجودة'];
                return  RespondWithBadRequestWithData($category_valid);
            }
        }

        $category = new Category();
        $category->name_ar = $name_ar;
        $category->name_en =  $name_en;
        $category->description_ar = $description_ar;
        $category->description_en =  $description_en;
        $category->code = $code;
        $category->is_freeze = $is_freeze;
        $category->parent_id =  $parent_id;
        $category->created_by = Auth::guard('admin')->user()->id;

        $category->save();
        if ($request->hasFile('image')) {

            UploadFile('images/categories', 'image', $category, $image);
        }

        return RespondWithSuccessRequest($lang, 1);
    }

    public function update(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        // Validate the input
        if ($request->hasFile('image')) {
            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string',
                'name_en' => 'nullable|string',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg',
                'is_freeze' => 'required|boolean',
                'parent_id' => 'nullable|exists:categories,id',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string',
                'name_en' => 'nullable|string',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'image' => 'nullable',
                'is_freeze' => 'required|boolean',
                'parent_id' => 'nullable|exists:categories,id',
            ]);
        }

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        // Retrieve the category by ID, or throw an exception if not found
        $category = Category::find($id);
        if (!$category) {
            return  RespondWithBadRequestData($lang, 8);
        }
        // dd($category, $request);
        if (
            $category->name_ar == $request->name_ar
            && $category->name_en == $request->name_en
            &&  $category->description_ar == $request->description_ar
            &&  $category->description_en == $request->description_en
            && $category->is_freeze == $request->is_freeze
            && $category->parent_id == $request->parent_id
            && $category->image == $request->file('image')
        ) {
            return  RespondWithBadRequestData($lang, 10);
        }
        $modify_by = Auth::guard('admin')->user()->id;

        // Assign the updated values to the category model
        $category->name_ar = $request->name_ar;
        $category->name_en = $request->name_en;
        $category->description_ar = $request->description_ar;
        $category->description_en = $request->description_en;
        $category->is_freeze = $request->is_freeze;
        $category->modify_by = $modify_by;
        $category->parent_id = isset($request->parent_id) && !empty($request->parent_id) ? $request->parent_id : null;

        // Handle file upload for the image if provided
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            DeleteFile('images/categories', $category->image);
            UploadFile('images/categories', 'image', $category, $image);
        }

        // Update the category in the database
        $category->save();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }

    public function delete(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        // Find the category by ID, or throw a 404 if not found
        $category = Category::find($id);
        if (!$category) {
            return  RespondWithBadRequestData($lang, 8);
        }
        // Check if there are any products associated with this category
        if ($category->products()->count() > 0) {
            return CustomRespondWithBadRequest(__('category.The category have relation'));
        }
        if ($category->children()->count()> 0) {
            return CustomRespondWithBadRequest(__('category.The category is referenced as a parent in another category and cannot be deleted.'));
        }



        // dd($category);
        // Handle deletion of associated image if it exists
        if ($category->image) {
            $imagePath = public_path('images/categories/' . $category->image);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }

        // Delete the category
        $category->delete();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
}
