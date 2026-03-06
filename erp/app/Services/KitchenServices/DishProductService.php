<?php

namespace App\Services\KitchenServices;

use App\Models\Branch;
use App\Models\Dish;
use App\Models\DishDetail;
use App\Models\Ingredient;
use App\Models\ProductUnit;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DishProductService
{
    public function store($data)
    {
        DB::beginTransaction();
        $data2 = $data->all();
        try {

            $GetLastID = GetLastID('dishes');
            $data2['code'] = GenerateCode('dishes', $GetLastID);

            $dish = Dish::create($data2);

            Log::info('Dish created', ['dish_id' => $dish->id]);

            if (isset($data2['branches']) && in_array('all', $data2['branches'])) {
                $allBranchIds = Branch::pluck('id')->toArray();
                // AddBranchMenu($dish->id, $allBranchIds);
            } elseif (isset($data2['branches'])) {
                // AddBranchMenu($dish->id, $data['branches']);
            }


            $GetLastRecepiID = GetLastID('recipes');
            $code = GenerateCode('recipes', $GetLastRecepiID);

            $addon = Recipe::create([
                'name_ar' => $data2['name_ar'],
                'name_en' => $data2['name_en'],
                'description_ar' => $data2['description_ar'],
                'description_en' => $data2['description_en'],
                'type' => 2,
                'time' => $data2['time'],
                'code' => $code, // Store the generated code here
                'is_active' => $data2['is_active'],
                'created_by' => auth('admin')->id(),
            ]);


            Ingredient::create([
                'recipe_id' => $addon->id,
                'product_id' => $data2['complete_product'],
                'product_unit_id' => ProductUnit::where('product_id', $data2['complete_product'])->firstOrFail()->id,
                'quantity' => 1,
                'loss_percent' => 0.00,
            ]);


            $image_path = $data->file('image');
            if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {

                UploadFile('images/addons', 'image', $dish, $image_path);
            }




            // if (isset($data['image'])) {

            //     function UploadFile($path, $image, $model, $request)

            //     $imagePath = UploadFile('images/addons','image', $dish,$data['image']);

            //     RecipeImage::create([
            //         'recipe_id' => $addon->id,
            //         'image_path' => $imagePath,
            //     ]);

            //     Log::info('Image added to addon', ['addon_id' => $addon->id, 'image_path' => $imagePath]);
            // }
            DishDetail::create([
                'dish_id' => $dish->id,
                'dish_size_id' => null,
                'recipe_id' => $addon->id,
                'quantity' => 1,
            ]);

            Log::info('Addon linked to dish', ['dish_id' => $dish->id, 'addon_id' => $addon->id]);

            DB::commit();

            return $dish;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating dish product', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
