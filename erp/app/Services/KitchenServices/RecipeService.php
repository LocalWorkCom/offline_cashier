<?php

namespace App\Services\KitchenServices;

use App\Models\Recipe;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\ProductUnit;
use App\Models\RecipeImage;
use App\Models\ProductBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RecipeService
{
    public function index($withTrashed = false)
    {

        return  $query = Recipe::with(['ingredients.product', 'images'])->where('type', 1);
    }

    public function show($id)
    {
        return Recipe::with(['ingredients.product', 'images'])->findOrFail($id);
    }

    public function getAllProducts()
    {
        $products = Product::all();
        Log::info('Fetching all raw products', [
            'count' => $products->count(),
            'products' => $products->toArray()
        ]);
        return $products;
    }

    public function store($data, $images)
    {
        // Log::info('Starting recipes creation', ['data' => $data]);
        
        // try {

            return DB::transaction(function () use ($data, $images) {
                // Generate code based on the last ID in the recipes table
                $GetLastID = GetLastID('recipes');
                $data['code'] = GenerateCode('recipes', $GetLastID);

                // Create the recipe
                $recipe = Recipe::create([
                    'name_ar' => $data['name_ar'],
                    'name_en' => $data['name_en'],
                    'description_ar' => $data['description_ar'] ?? null,
                    'description_en' => $data['description_en'] ?? null,
                    'type' => 1,
                    'time' => $data['time'],
                    'is_active' => $data['is_active'],
                    'created_by' =>  authActionSave()['by'],
                    'created_by_type' => authActionSave()['type'],
                    'code' => $data['code'],
                    // 'item_code_id' => $data['item_code_id'],
                ]);

                // Log::info('Recipe created', ['recipe_id' => $recipe->id]);

                $this->storeIngredients($recipe->id, $data['ingredients']);
                $this->storeImages($recipe->id, $images);
                $recipe->load(['ingredients.product', 'images']);
                $recipe->refresh();
                return $recipe;
            });
        // } catch (\Exception $e) {
        //     Log::error('Recipe creation failed', ['error' => $e->getMessage()]);
        //     throw $e; // Re-throw the exception after logging it
        // }
    }

    public function itemCodeList()
    {
        return getItemCodes();
    }

    public function update($id, $data, $images)
    {
        // Log::info('Starting recipe update', ['recipe_id' => $id, 'data' => $data]);

        $recipe = Recipe::with(['ingredients.product', 'images'])->findOrFail($id);

        if (!$recipe) {
            Log::error('Recipe not found', ['recipe_id' => $id]);
            $message = app()->getLocale() === 'ar' ? 'الوصفة غير موجودة' : 'Recipe not found';
            return RespondWithErrorMsg($message);
        }
        $recipe->update([
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'],
            'description_ar' => $data['description_ar'] ?? null,
            'description_en' => $data['description_en'] ?? null,
            'type' => 1,
            'time' => $data['time'],
            // 'item_code_id' => $data['item_code_id'],
            'is_active' => $data['is_active'],
            'modified_by' => authActionSave()['by'], // Handle modified by
            'modified_by_type' => authActionSave()['type'], // Handle modified by

        ]);

        Log::info('recipe details updated', ['recipe_id' => $recipe->id]);

        $this->updateIngredients($recipe->id, $data['ingredients']);
        $this->updateImages($recipe, $images);
        $recipe->load(['ingredients.product.product', 'images']);
        $recipe->refresh();
        return $recipe;
    }
    private function storeIngredients($recipeId, $ingredients)
    {
        foreach ($ingredients as $ingredientData) {
            try {
                // $productUnit = ProductUnit::where('product_id', $ingredientData['product_id'])->firstOrFail();
                // if (!$productUnit) {
                //     throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Product unit not found for product ID: ' . $ingredientData['product_id']);
                // }
                // Ingredient::create([
                //     'recipe_id' => $recipeId,
                //     'product_id' => $ingredientData['product_id'],
                //     'product_unit_id' => $productUnit->id,
                //     'quantity' => $ingredientData['quantity'],
                //     'loss_percent' => $ingredientData['loss_percent'] ?? 0.00,
                // ]);


                $productBrand = ProductBrand::where('id', $ingredientData['product_brand_id'])->firstOrFail();

                Ingredient::create([
                    'recipe_id' => $recipeId,
                    'product_brand_id' => $productBrand['id'],
                    'quantity' => $ingredientData['quantity'],
                    'loss_percent' => $ingredientData['loss_percent'] ?? 0.00,
                ]);

                Log::info('Ingredient added to recipe', ['recipe_id' => $recipeId, 'ingredient_data' => $ingredientData]);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                Log::error('Skipping ingredient. Product unit not found', [
                    'product_id' => $ingredientData['product_id']
                ]);
                continue;
            }
        }
    }
    private function storeImages($recipeId, $images)
    {
        // if ($images) {
        //     foreach ($images as $image) {
        //         $model =   RecipeImage::create([
        //             'recipe_id' => $recipeId
        //         ]);
        //         $imagePath = 'images/recipes/' . $image->getClientOriginalName();

        //         UploadFile($imagePath, 'image_path', $model, $image);
        //         // $image->move(public_path('images/addons'), $image->getClientOriginalName());


        //         Log::info('Image added to recipe', ['recipe_id' => $recipeId, 'image_path' => $imagePath]);
        //     }
        // }

        if (!empty($images)) {
            foreach ($images as $image) {
                if (!$image || !$image->isValid()) {
                    continue; // skip invalid files
                }
                $model = RecipeImage::create([
                    'recipe_id' => $recipeId
                ]);
                $imagePath = 'images/recipes/' . $image->getClientOriginalName();
                UploadFile($imagePath, 'image_path', $model, $image);
            }
        }
    }

    private function updateIngredients($recipeId, $ingredients)
    {
        Ingredient::where('recipe_id', $recipeId)->delete();

        $this->storeIngredients($recipeId, $ingredients);
    }

    private function updateImages($recipe, $images)
    {
        if ($images) {
            foreach ($recipe->images as $image) {
                $oldImagePath = public_path($image->image_path);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
                $image->delete();
            }

            $this->storeImages($recipe->id, $images);
        }
    }


    public function rules()
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'time' => 'nullable|integer',
            'description_ar' => 'required|string',
            'description_en' => 'required|string',
            'type' => 'required|integer|in:1,2',
            'is_active' => 'required|integer|in:1,2',

            // 'item_code_id' => 'required|integer|exists:item_codes,id',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.product_id' => 'required|exists:products,id',
            'ingredients.*.quantity' => 'required|numeric|min:0',
            'ingredients.*.loss_percent' => 'nullable|numeric|min:0|max:100',
            'images.*' => 'nullable|image|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'ingredients.required' => __('recipes.Please provide at least one ingredient.'),
            'ingredients.array' => __('Ingredients must be provided as an array.'),
            'ingredients.min' => __('Please provide at least one ingredient.'),
            'ingredients.*.product_id.required' => __('Each ingredient must have a valid product.'),
            'ingredients.*.product_id.exists' => __('The selected product does not exist.'),
            'ingredients.*.quantity.required' => __('Each ingredient must have a quantity.'),
            'ingredients.*.quantity.numeric' => __('Quantity must be a number.'),
            'ingredients.*.quantity.min' => __('Quantity must be at least 0.'),
            'ingredients.*.loss_percent.numeric' => __('Loss percentage must be a number.'),
            'ingredients.*.loss_percent.min' => __('Loss percentage must be at least 0.'),
            'ingredients.*.loss_percent.max' => __('Loss percentage cannot exceed 100.'),
            'images.*.image' => __('Uploaded files must be images.'),
            'images.*.max' => __('Images must not exceed 2 MB in size.'),
        ];
    }


    public function delete(Request $request, $id)
    {
        $lang = app()->getLocale();

        $recipe = Recipe::where('type', 1)->findOrFail($id); // This already throws a 404 if not found

        if ($recipe->hasDishRelation()) {
            $message = $lang === 'ar' ? 'الوصفة مرتبطة بأطباق أخرى' : 'The recipe is related to other dishes';
            return respondError($message, 404);

            // return CustomRespondWithBadRequest(__('recipes.The recipes have relation with dish'));
        }

        // Handle deleted_by
        $recipe->update([
            'deleted_by' =>  authActionSave()['by'], // Handle modified by
            'deleted_by_type' => authActionSave()['type'],
        ]);

        // Delete related ingredients
        Ingredient::where('recipe_id', $recipe->id)->delete();

        // Delete related images
        foreach ($recipe->images as $image) {
            if (Storage::exists($image->image_path)) {
                Storage::delete($image->image_path);
            }
            $image->delete();
        }

        // Soft delete recipe
        $recipe->delete();

        return RespondWithSuccessRequest($lang, 1);
    }


    public function restore($id)
    {
        $recipe = Recipe::withTrashed()->findOrFail($id);
        $recipe->restore();

        return $recipe;
    }
}
