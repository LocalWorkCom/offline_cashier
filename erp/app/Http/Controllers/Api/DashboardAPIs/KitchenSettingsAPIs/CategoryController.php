<?php

namespace App\Http\Controllers\Api\DashboardAPIs\KitchenSettingsAPIs;

use App\Http\Controllers\Controller;

use App\Models\Category;
use App\Models\ProductBrandCategoryColor;
use App\Models\ProductBrandCategorySize;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $categories = Category::with([
            'categorySizes' => function ($query) {
                $query->select('id', 'category_id', 'size_id', 'created_by');
            },
            'categoryColors' => function ($query) {
                $query->select('id', 'category_id', 'color_id', 'created_by');
            }
        ])->get();

        // Apply visibility settings to each category
        $categories->each(function ($category) {
            $category->makeVisible([
                'name_ar',
                'name_en',
                'description_ar',
                'description_en',
                'image',
                'created_at',
                'created_by'
            ]);
            $category->setAppends([]);
        });

        return ResponseWithSuccessData($lang, $categories, 1);
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg',
            'is_freeze' => 'required|boolean',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'sizes' => 'nullable|array',
            'sizes.*' => 'integer|exists:sizes,id',
            'colors' => 'nullable|array',
            'colors.*' => 'integer|exists:colors,id',
        ]);

        if ($validator->fails()) {
            return respondError('Validation Error .', 404, $validator->errors());
        }

        if (
            CheckExistColumnValue('categories', 'name_ar', $request->name_ar) ||
            CheckExistColumnValue('categories', 'name_en', $request->name_en)
        ) {
            $message = ($lang == 'ar') ? 'اسم الفئة موجود مسبقًا.' : 'Category name already exists.';
            return respondError($message, 400);
        }
        // $created_by = Auth::guard('api')->user()->id;
        $user = auth('employee')->user();

        // Create the category
        $GetLastID = GetLastID('categories');
        $category = new Category();
        $category->name_ar = $request->name_ar;
        $category->name_en = $request->name_en;
        $category->description_ar = $request->description_ar;
        $category->description_en = $request->description_en;
        $category->code = GenerateCode('categories', ($GetLastID == 1) ? 0 : $GetLastID);
        $category->is_freeze = $request->is_freeze;
        $category->parent_id = $request->parent_id ?: null; // Ensure null if empty
        $category->created_by =  $user->id;
        $category->image = $request->file('image');  // Handle file upload if necessary

        $category->save();

        // Handle image upload after saving the category
        if ($request->hasFile('image')) {

            UploadFile('images/categories', 'image', $category, $category->image);
        }


        // Attach sizes
        if ($request->has('sizes')) {
            foreach ($request->sizes as $sizeId) {
                $category->categorySizes()->create([
                    'size_id' => $sizeId,
                    'created_by' => $category->created_by
                ]);
            }
        }

        // Attach colors
        if ($request->has('colors')) {
            foreach ($request->colors as $colorId) {
                $category->categoryColors()->create([
                    'color_id' => $colorId,
                    'created_by' => $category->created_by
                ]);
            }
        }

        // Prepare response
        $category->makeVisible([
            'name_ar',
            'name_en',
            'description_ar',
            'description_en',
            'image',
            'created_at',
            'created_by'
        ]);
        $category->setAppends([]);
        $category->load([
            'categorySizes' => function ($query) {
                $query->select('id', 'category_id', 'size_id', 'created_by');
            },
            'categoryColors' => function ($query) {
                $query->select('id', 'category_id', 'color_id', 'created_by');
            }
        ]);
        return ResponseWithSuccessData($lang, $category, 1);
    }
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        // Validate the input
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_freeze' => 'required|boolean',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'sizes' => 'nullable|array',
            'sizes.*' => 'integer|exists:sizes,id',
            'colors' => 'nullable|array',
            'colors.*' => 'integer|exists:colors,id',
        ]);

        if ($validator->fails()) {
            return respondError('Validation Error .', 404, $validator->errors());
        }

        // Retrieve the category with relationships
        $category = Category::with(['categorySizes', 'categoryColors'])->find($id);
        if (!$category) {
            $message = ($lang == 'ar') ? 'الفئة غير موجودة.' : 'Category not found.';
            return respondError($message, 400);
        }

        // Check if data is actually changing
        if (
            $category->name_ar == $request->name_ar &&
            $category->name_en == $request->name_en &&
            $category->description_ar == $request->description_ar &&
            $category->description_en == $request->description_en &&
            $category->is_freeze == $request->is_freeze &&
            $category->parent_id == $request->parent_id &&
            !$request->hasFile('image') &&
            empty(array_diff($request->sizes ?? [], $category->categorySizes->pluck('size_id')->toArray())) &&
            empty(array_diff($request->colors ?? [], $category->categoryColors->pluck('color_id')->toArray())) &&
            empty(array_diff($category->categorySizes->pluck('size_id')->toArray(), $request->sizes ?? [])) &&
            empty(array_diff($category->categoryColors->pluck('color_id')->toArray(), $request->colors ?? []))
        ) {
            $message = ($lang == 'ar') ? 'لا توجد تغييرات لتحديث الفئة.' : 'No changes to update the category.';
            return respondError($message, 400);
        }

        // Check for duplicate names (excluding current category)
        if (
            CheckExistColumnValue('categories', 'name_ar', $request->name_ar, $id) ||
            CheckExistColumnValue('categories', 'name_en', $request->name_en, $id)
        ) {
            $message = ($lang == 'ar') ? 'اسم الفئة موجود مسبقًا.' : 'Category name already exists.';
            return respondError($message, 400);
        }

        $user = auth('employee')->user();

        // Update category fields
        $category->name_ar = $request->name_ar;
        $category->name_en = $request->name_en;
        $category->description_ar = $request->description_ar;
        $category->description_en = $request->description_en;
        $category->is_freeze = $request->is_freeze;
        $category->parent_id = $request->parent_id ?: null;
        $category->modify_by = $user->id;

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($category->image) {
                DeleteFile('images/categories', $category->image);
            }
            $image = $request->file('image');
            UploadFile('images/categories', 'image', $category, $image);
            $updateData['image'] = $image;
        }
        $category->save();

        // Sync sizes - only update what's changed
        if ($request->has('sizes')) {
            $currentSizes = $category->categorySizes->pluck('size_id')->toArray();
            $newSizes = $request->sizes;

            // Sizes to remove
            $sizesToRemove = array_diff($currentSizes, $newSizes);
            if (!empty($sizesToRemove)) {
                $category->categorySizes()->whereIn('size_id', $sizesToRemove)->delete();
            }

            // Sizes to add
            $sizesToAdd = array_diff($newSizes, $currentSizes);
            foreach ($sizesToAdd as $sizeId) {
                $category->categorySizes()->firstOrCreate([
                    'size_id' => $sizeId
                ], [
                    'created_by' => $user->id
                ]);
            }
        }

        // Sync colors - only update what's changed
        if ($request->has('colors')) {
            $currentColors = $category->categoryColors->pluck('color_id')->toArray();
            $newColors = $request->colors;

            // Colors to remove
            $colorsToRemove = array_diff($currentColors, $newColors);
            if (!empty($colorsToRemove)) {
                $category->categoryColors()->whereIn('color_id', $colorsToRemove)->delete();
            }

            // Colors to add
            $colorsToAdd = array_diff($newColors, $currentColors);
            foreach ($colorsToAdd as $colorId) {
                $category->categoryColors()->firstOrCreate([
                    'color_id' => $colorId
                ], [
                    'created_by' => $user->id
                ]);
            }
        }

        // Refresh the model with relationships
        $category->load([
            'categorySizes' => function ($query) {
                $query->select('id', 'category_id', 'size_id', 'created_by');
            },
            'categoryColors' => function ($query) {
                $query->select('id', 'category_id', 'color_id', 'created_by');
            }
        ]);

        // Prepare response
        $category->makeVisible([
            'name_ar',
            'name_en',
            'description_ar',
            'description_en',
            'image',
            'created_at',
            'created_by'
        ]);
        $category->setAppends([]);

        return ResponseWithSuccessData($lang, $category, 1);
    }
    public function delete(Request $request, $id)
    {
        // Fetch the language header for response
        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided
        App::setLocale($lang);
        // Find the category by ID, or throw a 404 if not found
        $category = Category::find($id);
        if (!$category) {
            $message = ($lang == 'ar') ? 'الفئة غير موجودة.' : 'Category not found.';
            return respondError($message, 400);
        }
        // Check if there are any products associated with this category
        if ($category->products()->count() > 0) {
            $message = ($lang == 'ar') ? 'لا يمكن حذف الفئة لأنها مرتبطة بمنتجات.' : 'Cannot delete category as it is associated with products.';
            return respondError($message, 400);
        }

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
    public function product_color_store(Request $request)
    {
        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided
        App::setLocale($lang);
        $validated = $request->validate([
            'product_brand_id' => 'required|exists:product_brands,id',
            'category_color_id' => 'required|exists:category_colors,id',
        ]);

        $record = ProductBrandCategoryColor::create($validated);

        return ResponseWithSuccessData($lang, $record, 1);
    }


    public function product_size_store(Request $request)
    {
        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided
        App::setLocale($lang);
        $validated = $request->validate([
            'product_brand_id' => 'required|exists:product_brands,id',
            'category_size_id' => 'required|exists:category_sizes,id',
        ]);

        $record = ProductBrandCategorySize::create($validated);

        return ResponseWithSuccessData($lang, $record, 1);
    }
}
