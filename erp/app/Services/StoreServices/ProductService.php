<?php

namespace App\Services\StoreServices;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductSize;
use App\Models\ProductUnit;
use App\Models\ProductColor;
use App\Models\ProductImage;
use App\Models\ProductStore;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductService
{

    // In ProductService.php

    // Helper method to check if the product data hasn't changed
    protected function isProductUnchanged($product, $request)
    {
        return $product->name_ar == $request->name_ar &&
            $product->name_en == $request->name_en &&
            $product->description_ar == $request->description_ar &&
            $product->description_en == $request->description_en &&
            $product->is_have_expired == $request->is_have_expired &&
            $product->type == $request->type &&
            $product->is_remind == $request->is_remind &&
            $product->main_unit_id == $request->main_unit_id &&
            $product->currency_code == $request->currency_code &&
            $product->category_id == $request->category_id &&
            $product->sku == $request->sku &&
            $product->barcode == $request->barcode;
    }

    // Method to get all products with their limits
    public function index(Request $request, $checkToken)
    {
        $lang = app()->getLocale();
        $products = Product::all();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        if (!$checkToken) {
            $products = $products->makeVisible(['name_en', 'name_ar', 'main_image', 'description_ar', 'description_en']);
        }

        return ResponseWithSuccessData($lang, $products, 1);
    }

    public function create(Request $request, $checkToken)
    {
        $lang = app()->getLocale();
        $products = Product::all();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        return ResponseWithSuccessData($lang, $products, 1);
    }

    // Method to store a new product
    public function store(Request $request, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'main_image' => 'required|file|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_have_expired' => 'required|boolean',
            'type' => 'required|string|in:complete,raw',
            'is_remind' => 'required|boolean',

            'sku' => [
                'required',
                'string',
                Rule::unique('products')->whereNull('deleted_at')
            ],
            'barcode' => [
                'required',
                'string',
                Rule::unique('products')->whereNull('deleted_at')
            ],

            // 'sku' => 'required|string|unique:products',
            // 'barcode' => 'required|string|unique:products',
            'main_unit_id' => 'required|integer',
            // 'factor' => 'required|numeric|min:0',
            'currency_code' => 'required|string',
            'category_id' => 'required|integer',
            'min_limit' => 'required|integer|min:0',
            'max_limit' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        // Check if product or category name already exists
        if (CheckExistColumnValue('products', 'name_ar', $request->name_ar) || CheckExistColumnValue('categories', 'name_en', $request->name_en)) {
            return RespondWithBadRequest($lang, 9);
        }

        // Check if category exists
        $category = Category::find($request->category_id);
        if (!$category) {
            return RespondWithBadRequestData($lang, 8);
        }
        // Check if brand exists
        $brand = Brand::find($request->brand_id);
        if (!$brand) {
            return RespondWithBadRequestData($lang, 8);
        }

        // Generate product code
        $GetLastID = GetLastID('products');
        $code = GenerateCode('products', $GetLastID);

        // Create a new product
        $product = new Product();
        $product->name_ar = $request->name_ar;
        $product->name_en = $request->name_en;
        $product->description_ar = $request->description_ar;
        $product->description_en = $request->description_en;
        $product->code = $code;
        $product->brand_id = $request->brand_id;
        $product->type = $request->type;
        $product->sku = $request->sku;
        $product->barcode = $request->barcode;
        $product->is_have_expired = $request->is_have_expired;
        $product->is_remind = $request->is_remind;
        $product->main_unit_id = $request->main_unit_id;
        $product->currency_code = $request->currency_code;
        $product->category_id = $request->category_id;
        $product->created_by = Auth::guard('admin')->user()->id;
        $product->save();

        // Save product limits
        $product_limit = new ProductStore();
        $product_limit->product_id = $product->id;
        $product_limit->min_limit = $request->min_limit;
        $product_limit->max_limit = $request->max_limit;
        $product_limit->store_id = $request->store_id;
        $product_limit->save();

        // Save product limits
        //  $product_unit = new ProductUnit();
        //  $product_unit->product_id = $product->id;
        //  $product_unit->factor = $request->factor;
        //  $product_unit->unit_id = $request->main_unit_id;
        //  $product_unit->created_by = Auth::guard('admin')->user()->id;

        //  $product_unit->save();

        // Handle file upload (main image)
        if ($request->hasFile('main_image')) {
            $main_image = $request->file('main_image');
            UploadFile('images/products', 'main_image', $product, $main_image);
        }

        // Handle gallery images
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $validator = Validator::make($request->all(), [
                'images.*' => 'mimes:jpeg,jpg,png,gif,svg|max:2048'
            ]);

            if ($validator->fails()) {
                return RespondWithBadRequestWithData($validator->errors());
            }

            foreach ($images as $image) {
                $product_image = new ProductImage();
                $product_image->product_id = $product->id;
                $product_image->created_by = Auth::guard('admin')->user()->id;

                $product_image->save();
                UploadFile('images/products/gallery', 'image', $product_image, $image);
            }
        }

        return RespondWithSuccessRequest($lang, 1);
    }

    // Method to update an existing product
    public function update(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'main_image' => $request->hasFile('main_image') ? 'file|image|mimes:jpeg,png,jpg,gif,svg|max:2048' : 'nullable',
            'is_have_expired' => 'required|boolean',
            'type' => 'required|string|in:complete,raw',
            'is_remind' => 'required|boolean',
            'sku' => 'required|string',
            'barcode' => 'required|string',
            'main_unit_id' => 'required|integer',
            'currency_code' => 'required|string',
            'category_id' => 'required|integer',
            'images.*' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
            'min_limit' => 'nullable|numeric|min:0',
            'max_limit' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            if ($validator->errors()->has('main_image')) {
                return CustomRespondWithBadRequest(__('product.Invalid image file or format.'));
            }
            if ($validator->errors()->has('images.*')) {
                return CustomRespondWithBadRequest(__('product.Invalid image file or format.'));
            }
            return RespondWithBadRequestWithData($validator->errors());
        }

        $product = Product::find($id);
        if (!$product) {
            return RespondWithBadRequestData($lang, 8);
        }

        // Check for unique constraints
        $existingProduct2 = Product::where('barcode', $request->barcode)
            ->where('id', '!=', $id)
            ->whereNull('deleted_at') // Only check active products
            ->first();

        if ($existingProduct2) {
            return CustomRespondWithBadRequest(__('product.This barcode is already in use.'));
        }

        if ($existingProduct2) {
            return CustomRespondWithBadRequest(__('product.This barcode is already in use.'));
        }



        $existingProduct = Product::where('sku', $request->sku)
            ->where('id', '!=', $id)
            ->whereNull('deleted_at') // Ensure the product is not soft-deleted
            ->first();

        if ($existingProduct) {
            return CustomRespondWithBadRequest(__('product.This SKU is already in use.'));
        }


        // Update product details
        $product->update($request->only([
            'name_ar',
            'name_en',
            'description_ar',
            'description_en',
            'type',
            'is_have_expired',
            'is_remind',
            'sku',
            'barcode',
            'main_unit_id',
            'currency_code',
            'category_id',
        ]));

        // Update ProductStore
        $product_limit = ProductStore::firstOrNew(['product_id' => $product->id]);
        $product_limit->min_limit = $request->min_limit;
        $product_limit->max_limit = $request->max_limit;
        $product_limit->store_id = $request->store_id;
        $product_limit->save();

        // Handle image uploads
        if ($request->hasFile('main_image')) {
            $main_image = $request->file('main_image');
            DeleteFile('images/products', $product->main_image);
            UploadFile('images/products', 'main_image', $product, $main_image);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $productImage = new ProductImage();
                $productImage->product_id = $product->id;
                $productImage->created_by = Auth::guard('admin')->user()->id;
                $productImage->save();
                UploadFile('images/products/gallery', 'image', $productImage, $image);
            }
        }

        // Soft delete selected images
        $removeImageIds = $request->input('remove_image_ids', []);
        foreach ($removeImageIds as $imageId) {
            $image = ProductImage::find($imageId);
            if ($image) {
                $image->deleted_by = Auth::id();
                $image->delete();
            }
        }

        // if ($product->wasChanged() || $product_limit->wasChanged()) {
        return RespondWithSuccessRequest($lang, 1);
        // }
        // else {
        //     return RespondWithBadRequest($lang, 10);
        // }
    }



    public function delete(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $product = Product::withTrashed()->find($id);
        if (!$product) {
            return RespondWithBadRequestData($lang, 8);
        }

        // Force delete the product instead of soft delete
        $product->forceDelete();

        return RespondWithSuccessRequest($lang, 1);
    }

    // product unit
    public function list($productId, $checkToken)
    {
        $lang = app()->getLocale();
        $products = Product::with(['itemCode','productUnits.unit' => function ($query) {
            $query->select('id', 'name_ar');  // Select only 'id' and 'name_ar' columns from the 'units' table
        }])->findOrFail($productId);
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        return ResponseWithSuccessData($lang, $products, 1);
    }
    public function saveProductUnits(Request $request, $productId)
    {
        // Validate the input
        $lang = app()->getLocale();
        $validated = $request->validate([
            'units.*.unit_id' => 'required|exists:units,id',
            'units.*.factor' => 'required|numeric',
            'product_unit_id' => 'nullable|array',
            'product_unit_id.*' => 'nullable|integer|exists:product_units,id',
        ]);

        // Ensure product_unit_id is an array, defaulting to empty array if not present
        $productUnitIds = $request->product_unit_id ?? [];

        // Ensure units is an array, defaulting to empty array if not present
        $units = $request->units ?? [];

        // Get all existing product units for the given product
        $productUnits = ProductUnit::where('product_id', $productId)->get();

        // Delete units that are not in the submitted product_unit_ids
        foreach ($productUnits as $unit) {
            if (!in_array($unit->id, $productUnitIds)) {
                $unit->delete();
            }
        }

        // Save or update units
        foreach ($units as $index => $unit) {
            $unitId = (int) $unit['unit_id'];
            $factor = $unit['factor'];
            $productUnitId = $productUnitIds[$index] ?? null;

            // Check for duplicate units, excluding the current unit being updated
            $existingUnitQuery = ProductUnit::where('product_id', $productId)
                ->where('unit_id', $unitId);
            if ($productUnitId) {
                $existingUnitQuery->where('id', '!=', $productUnitId);
            }
            $existingUnit = $existingUnitQuery->exists();

            if ($existingUnit) {
                // If the unit already exists for this product, return a flash message
                return CustomRespondWithBadRequest(__('unit.The unit with ID already exists for this product.'));
            }

            if ($productUnitId) {
                // Update existing unit
                ProductUnit::updateOrCreate(
                    ['id' => $productUnitId],
                    [
                        'unit_id' => $unitId,
                        'factor' => $factor,
                        'product_id' => $productId,
                    ]
                );
            } else {
                // Create new unit
                ProductUnit::create([
                    'unit_id' => $unitId,
                    'factor' => $factor,
                    'product_id' => $productId,
                    'created_by' => Auth::guard('admin')->user()->id,
                ]);
            }
        }

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }

    // product size
    public function listSize($productId, $checkToken)
    {
        $lang = app()->getLocale();
        $products = Product::with(['productSizes.size' => function ($query) {
            $query->select('id', 'name_ar');  // Select only 'id' and 'name_ar' columns from the 'units' table
        }])->findOrFail($productId);
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        return ResponseWithSuccessData($lang, $products, 1);
    }
    public function saveProductSizes(Request $request, $productId)
    {
        $lang = app()->getLocale();

        // Validate the sizes
        $validator = Validator::make($request->all(), [
            'sizes.*.size_id' => 'required|exists:sizes,id',
            // 'sizes.*.code_size' => 'required|string|unique:product_sizes,code_size',
            'product_size_id' => 'nullable|array',  // Ensure product_size_id is an array if passed
            'sizes.*.code_size' => [
                'required',
                'string'
            ],
        ]);

        if ($validator->fails()) {
            // Check for unique code error specifically
            if ($validator->errors()->has('code_size')) {
                return CustomRespondWithBadRequest('Duplicate code_size are submitted.');
            }

            // Return other validation errors
            return RespondWithBadRequestData($lang, $validator->errors());
        }
        if (CheckExistColumnValue('product_sizes', 'code_size', $request->code_size)) {
            return RespondWithBadRequest($lang, 9);
        }
        $product_size_ids = $request->product_size_id; // This is an array of product size IDs, or null if creating new sizes

        // Get all the existing product sizes for the given product
        $product_sizes = ProductSize::where('product_id', $productId)->get();

        // Remove sizes that are not in the submitted product_size_ids
        // Ensure $product_size_ids is an array before checking
        if (is_array($product_size_ids)) {
            foreach ($product_sizes as $size) {
                // If the size ID is not in the submitted product_size_ids, delete it
                if (!in_array($size->id, $product_size_ids)) {
                    $size->delete();
                }
            }
        } else {
            // If product_size_ids is not an array or is null, you can delete all associated product sizes
            foreach ($product_sizes as $size) {
                $size->delete();
            }
        }

        // Process the incoming sizes to create/update
        $sizes = $request->get('product_sizes', []);  // Default to empty array if no sizes are provided

        // Ensure the sizes array is not empty before proceeding
        if (!empty($sizes)) {
            // Loop through the sizes and save them
            for ($i = 0; $i < count($sizes); $i++) {
                $sizeId = (int) $sizes[$i]['size_id'];  // Cast size_id to integer
                $code_size = $sizes[$i]['code_size'];  // code_size value

                // Check if the size already exists for the given product
                if (isset($product_size_ids[$i]) && $product_size_ids[$i]) {
                    // Update existing ProductSize
                    ProductSize::updateOrCreate(
                        ['id' => (int) $product_size_ids[$i]],  // Find the record by its ID
                        [
                            'size_id' => $sizeId,  // Update size_id
                            'code_size' => $code_size,  // Update code_size
                        ]
                    );
                } else {
                    // Check if the size already exists for the given product
                    $existingSize = ProductSize::where('product_id', $productId)
                        ->where('size_id', $sizeId)
                        ->exists();

                    if ($existingSize) {
                        // If the size already exists for this product, return a flash message
                        return CustomRespondWithBadRequest('The size with ID ' . $sizeId . ' already exists for this product.');
                    }

                    // Check if the code_size already exists, excluding soft deleted records
                    $existingSizeWithCode = ProductSize::where('code_size', $code_size)
                        ->whereNull('deleted_at') // Exclude soft deleted records by checking for null in the deleted_at field
                        ->exists();

                    if ($existingSizeWithCode) {
                        // Return error if the code_size already exists for this product
                        return CustomRespondWithBadRequest('The size with code size ' . $code_size . ' already exists for this product.');
                    }

                    // If no existing size or code_size is found, create a new ProductSize
                    ProductSize::create([
                        'size_id' => (int) $sizeId,  // Ensure size_id is treated as an integer
                        'product_id' => (int) $productId,  // Ensure product_id is treated as an integer
                        'created_by' => Auth::guard('admin')->user()->id,  // Keep created_by as is
                        'code_size' => $code_size,  // code_size value
                    ]);
                }
            }
        }

        // Return a success response after all sizes are processed
        return RespondWithSuccessRequest($lang, 1);
    }
    // product color
    public function listColor($productId, $checkToken)
    {
        $lang = app()->getLocale();
        $products = Product::with(['productColors.color' => function ($query) {
            $query->select('id', 'name_ar');  // Select only 'id' and 'name_ar' columns from the 'units' table
        }])->findOrFail($productId);
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        return ResponseWithSuccessData($lang, $products, 1);
    }
    public function saveProductColors(Request $request, $productId)
    {
        // Validate the input
        $lang = app()->getLocale();
        $validated = $request->validate([
            'colors.*.color_id' => 'required|exists:colors,id',
            'product_color_id' => 'nullable|array',
            'product_color_id.*' => 'nullable|integer|exists:product_colors,id',
        ]);

        // Ensure product_color_id is always an array, even if not provided
        $productColorIds = $request->product_color_id ?? [];

        // Ensure colors is always an array, even if not provided
        $colors = $request->colors ?? [];

        // Fetch current product colors
        $productColors = ProductColor::where('product_id', $productId)->get();

        // Collect submitted color IDs
        $submittedColorIds = collect($colors)->pluck('color_id')->toArray();

        // Check for duplicates among submitted colors
        if (count($submittedColorIds) !== count(array_unique($submittedColorIds))) {
            return CustomRespondWithBadRequest('Duplicate colors are submitted.');
        }

        // Delete colors that are not in the submitted product_color_ids
        foreach ($productColors as $color) {
            if (!in_array($color->id, $productColorIds)) {
                $color->delete();
            }
        }

        // Save or update colors
        foreach ($colors as $index => $color) {
            $colorId = (int) $color['color_id'];  // Ensure color_id is an integer
            $productColorId = isset($productColorIds[$index]) ? $productColorIds[$index] : null;

            // Check if the same color already exists for this product
            $existingColor = ProductColor::where('product_id', $productId)
                ->where('color_id', $colorId)
                ->where('id', '!=', $productColorId) // Exclude the current color being updated
                ->exists();

            if ($existingColor) {
                return CustomRespondWithBadRequest(
                    'The color with ID ' . $colorId . ' already exists for this product.'
                );
            }

            if ($productColorId) {
                // Update existing color
                ProductColor::updateOrCreate(
                    ['id' => $productColorId],
                    ['color_id' => $colorId, 'product_id' => $productId]
                );
            } else {
                // Create new color
                ProductColor::create([
                    'color_id' => $colorId,
                    'product_id' => $productId,
                    'created_by' => Auth::guard('admin')->user()->id,
                ]);
            }
        }

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
}
