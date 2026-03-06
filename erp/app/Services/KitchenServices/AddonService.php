<?php

namespace App\Services\KitchenServices;

use App\Models\Recipe;
use App\Models\Product;
use App\Models\Ingredient;
use App\Models\ProductBrand;
use App\Models\ProductUnit;
use App\Models\RecipeImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AddonService
{
    public function index($withTrashed = false)
    {

        $query = Recipe::where('type', 2)->with(['ingredients.product', 'images']);

        return $withTrashed ? $query->withTrashed() : $query->whereNull('deleted_at');
    }

    public function show($id)
    {
        return Recipe::with(['ingredients.product', 'images'])
            ->where('type', 2)
            ->findOrFail($id);
    }

    public function getAllProducts()
    {
        $products = Product::all();
        Log::info('Fetching all products', [
            'count' => $products->count(),
            'products' => $products->toArray(),
        ]);

        return $products;
    }

    public function getAllProductBrands()
    {
        $products = ProductBrand::all();
        Log::info('Fetching all products', [
            'count' => $products->count(),
            'products' => $products->toArray(),
        ]);

        return $products;
    }

    public function store($data, $images)
    {
        return DB::transaction(function () use ($data, $images) {

            // Generate code based on the last ID in the recipes table
            $GetLastID = GetLastID('recipes');
            $data['code'] = GenerateCode('recipes', $GetLastID);
            // Create the addon (ensuring type = 2)
            $addon = Recipe::create([
                'name_ar' => $data['name_ar'],
                'name_en' => $data['name_en'],
                'description_ar' => $data['description_ar'] ?? null,
                'description_en' => $data['description_en'] ?? null,
                'time' => $data['time'] ?? null,
                'type' => 2, // Ensures it's always an addon
                'is_active' => $data['is_active'],
                'created_by' =>  authActionSave()['by'],
                'created_by_type' => authActionSave()['type'],
                'code' => $data['code'], // Store the generated code here
            ]);

            Log::info('Addon created', ['addon_id' => $addon->id]);


            $this->storeIngredients($addon->id, $data['ingredients']);
            $this->storeImages($addon->id, $images);
            $addon->refresh();
            $addon->load(['ingredients.product.product', 'images']);
            return $addon;
        });
    }

    private function storeIngredients($addonId, $ingredients)
    {
        foreach ($ingredients as $ingredientData) {
            $productBrand = ProductBrand::where('id', $ingredientData['product_brand_id'])->firstOrFail();
            Ingredient::create([
                'recipe_id' => $addonId,
                'product_brand_id' => $ingredientData['product_brand_id'],
                'quantity' => $ingredientData['quantity'],
                'loss_percent' => $ingredientData['loss_percent'] ?? 0.00,
            ]);

            Log::info('Ingredient added to addon', ['addon_id' => $addonId, 'ingredient_data' => $ingredientData]);
        }
    }

    private function storeImages($addonId, $images)
    {
        if ($images) {
            foreach ($images as $image) {
                $model =   RecipeImage::create([
                    'recipe_id' => $addonId
                ]);
                $imagePath = 'images/addons/' . $image->getClientOriginalName();

                UploadFile($imagePath, 'image_path', $model, $image);
                // $image->move(public_path('images/addons'), $image->getClientOriginalName());


                Log::info('Image added to addon', ['addon_id' => $addonId, 'image_path' => $imagePath]);
            }
        }
    }
    public function update($id, $data, $images)
    {
        Log::info('Starting addon update', ['addon_id' => $id, 'data' => $data]);

        $addon = Recipe::findOrFail($id);

        $addon->update([
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'],
            'description_ar' => $data['description_ar'] ?? null,
            'description_en' => $data['description_en'] ?? null,
            'type' => 2, // Ensures it remains an addon
            'is_active' => $data['is_active'],
            'modified_by' =>  authActionSave()['by'],
            'modified_by_type' => authActionSave()['type'],
        ]);

        Log::info('Addon details updated', ['addon_id' => $addon->id]);

        $this->updateIngredients($addon->id, $data['ingredients']);
        $this->updateImages($addon, $images);
        $addon->refresh();
        $addon->load(['ingredients.product.product', 'images']);
        return $addon;
    }


    public function delete(Request $request, $id)
    {
        $lang = app()->getLocale();
        // Find the addon by ID or fail if not found
        $addon = Recipe::where('type', 2)->findOrFail($id);

        // Check if the addon has any related dishes
        if ($addon->addons()->exists()) {
            return CustomRespondWithBadRequest(__('addons.The addon have relation with dish'));
        }

        // Mark the addon as deleted and soft delete it
        $addon->update([
            'deleted_by' => authActionSave()['by'],
            'deleted_by_type' => authActionSave()['type'],
        ]);

        // Delete associated ingredients
        Ingredient::where('recipe_id', $addon->id)->delete();

        // Delete associated images
        foreach ($addon->images as $image) {
            if (Storage::exists($image->image_path)) {
                Storage::delete($image->image_path);
            }
            $image->delete();
        }

        // Soft delete the addon
        $addon->delete();

        // Return a success response
        return RespondWithSuccessRequest($lang, 1);
    }


    public function restore($id)
    {
        $addon = Recipe::withTrashed()->findOrFail($id);
        $addon->restore();

        Log::info('Addon restored', ['addon_id' => $addon->id]);

        return $addon;
    }


    private function updateIngredients($addonId, $ingredients)
    {
        Ingredient::where('recipe_id', $addonId)->delete();

        $this->storeIngredients($addonId, $ingredients);
    }

    private function updateImages($addon, $images)
    {
        if ($images) {
            foreach ($addon->images as $image) {
                $oldImagePath = public_path($image->image_path);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
                $image->delete();
            }

            $this->storeImages($addon->id, $images);
        }
    }
}
