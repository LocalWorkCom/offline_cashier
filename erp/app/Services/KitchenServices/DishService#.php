<?php

namespace App\Services\KitchenServices;

use App\Models\Dish;
use App\Models\DishSize;
use App\Models\DishDetail;
use App\Models\DishAddon;
use App\Models\Branch;
use App\Models\DishIngredientStep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DishService
{
    public function index()
    {
        $query = Dish::with([
            'dishCategory',
            'cuisine',
            'itemCode',
            'sizes',
            'details.recipe',
            'dishAddonsDetails.addons',
            'dishAddonsDetails.category'
        ]);

        return $query;
    }

    public function show($id)
    {
        try {
            $dish = Dish::with([
                'itemCode',
                'dishCategory',
                'cuisine',
                'sizes.details.recipe',
                'details.recipe',
                'dishAddonsDetails.category',
                'dishAddonsDetails.addons',
            ])->find($id);

            return $dish;
        } catch (\Exception $e) {
            Log::error('Error Retrieving Dish', ['error' => $e->getMessage(), 'dish_id' => $id]);
            throw $e;
        }
    }

    public function store($data)
    {
        try {
            return DB::transaction(function () use ($data) {
                $validatedData = $this->validateDishData($data);

                // Set default values and system fields
                $validatedData['is_active'] = $validatedData['is_active'] ?? 1;
                $validatedData['has_sizes'] = $validatedData['has_sizes'] ?? 0;
                $validatedData['has_addon'] = $validatedData['has_addon'] ?? 0;

                // Generate code based on the last ID in dishes table
                $GetLastID = GetLastID('dishes');
                $validatedData['code'] = GenerateCode('dishes', $GetLastID);

                // Remove price if has_sizes is true
                if ($validatedData['has_sizes'] == 1) {
                    unset($validatedData['price']);
                }
                $validatedData['created_by'] = authActionSave()['by'];
                $validatedData['created_type'] =  authActionSave()['type'];

                // Create the main dish
                $dish = Dish::create($validatedData);

                // Process sizes if applicable
                if ($dish->has_sizes && isset($data['sizes'])) {
                    $this->createDishSizes($dish, $data['sizes'], $data['default_size'] ?? null);
                }

                // Process dish details/recipes if no sizes or for dishes without sizes
                if (!$dish->has_sizes && isset($data['details'])) {
                    $this->createDishDetails($dish, $data['details']);
                }

                // Process addons if applicable
                if ($dish->has_addon && isset($data['addon_categories'])) {
                    $this->createDishAddons($dish, $data['addon_categories']);
                }

                // Handle image upload
                if (isset($data['image'])) {
                    UploadFile('images/dishes', 'image', $dish, $data['image']);
                }

                // Handle branch assignments
                if (isset($data['branches'])) {
                    $this->assignDishToBranches($dish, $data['branches']);
                }

                $data = $dish->load([
                    'dishCategory',
                    'cuisine',
                    'itemCode',
                    'sizes',
                    'details.recipe',
                    'dishAddonsDetails'
                ]);

                return $data;
            });
        } catch (ValidationException $e) {
            Log::error('Validation Error in Dish Creation', ['errors' => $e->errors()]);
            return [
                'status' => false,
                'message' => 'Validation error',
                'data' => $e->errors(),
            ];
        } catch (\Exception $e) {
            Log::error('Dish Creation Failed', ['error' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => 'Server error occurred',
                'data' => $e->getMessage(),
            ];
        }
    }

    public function update( $data ,$id)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $dish = Dish::find($id);
                $lang = app()->getLocale();

                if (!$dish) {
                    $message = $lang == 'en' ? 'Dish not found' : 'الطبق غير موجود';
                    return respondError($message, 404);
                }

            //    return $data;
            //     // Validate the dish data
                // $data = json_decode($data, true);
                // dd($data);
                $validatedData = $this->validateDishData($data->all());

                $validatedData['modified_by'] = authActionSave()['by'];
                $validatedData['modified_type'] = authActionSave()['type'];

                // Remove price if has_sizes is true
                if ($validatedData['has_sizes'] == 1) {
                    $validatedData['price'] = null;
                }

                // Handle image upload
                if (isset($data['image'])) {
                    if ($dish->image) {
                        DeleteFile('images/dishes', $dish->image);
                    }
                    UploadFile('images/dishes', 'image', $dish, $data['image']);
                }

                // Update the dish
                $dish->update($validatedData);

                // Update sizes
                if ($dish->has_sizes && isset($data['sizes'])) {
                    $this->updateDishSizes($dish, $data['sizes'], $data['default_size'] ?? null);
                } else {
                    // Remove all sizes if has_sizes changed to false
                    $dish->sizes()->delete();
                }

                // Update dish details/recipes
                if (!$dish->has_sizes && isset($data['details'])) {
                    $this->updateDishDetails($dish, $data['details']);
                } else if ($dish->has_sizes) {
                    // Remove details that are not associated with sizes
                    $dish->details()->whereNull('dish_size_id')->delete();
                }

                // Update addons
                if ($dish->has_addon && isset($data['addon_categories'])) {
                    $this->updateDishAddons($dish, $data['addon_categories']);
                } else {
                    // Remove all addons if has_addon changed to false
                    $dish->dishAddonsDetails()->delete();
                }

                // Update branch assignments
                if (isset($data['branches'])) {
                    $this->assignDishToBranches($dish, $data['branches']);
                }

                $data = $dish->load([
                    'dishCategory',
                    'cuisine',
                    'itemCode',
                    'sizes',
                    'details.recipe',
                    'dishAddonsDetails'
                ]);

                return $data;
            });
        } catch (ValidationException $e) {
            Log::error('Validation Error in Dish Update', ['errors' => $e->errors()]);
            return [
                'status' => false,
                'message' => 'Validation error',
                'data' => $e->errors(),
            ];
        } catch (\Exception $e) {
            Log::error('Dish Update Failed', ['error' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => 'Server error occurred',
                'data' => $e->getMessage(),
            ];
        }
    }

    public function delete($id)
    {
        try {
            $lang = app()->getLocale();

            $dish = Dish::find($id);
            if (!$dish) {
                $message = $lang == 'en' ? 'Dish not found' : 'الطبق غير موجود';
                return respondError($message, 404);
            }

            // Check if dish has relations that prevent deletion
            if ($dish->orderDetails()->exists()) {
                $message = $lang == 'en' ? 'Cannot delete dish as it has associated orders' : 'لا يمكن حذف الطبق لأنه يحتوي على طلبات مرتبطة به';
                return respondError($message, 400);
            }

            $dish->update(['deleted_by' => authActionSave()['by'], 'deleted_type' => authActionSave()['type']]);
            $dish->delete();

            // Clean up related menu assignments
            DeleteMenu($id);

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            Log::error('Dish Deletion Failed', ['error' => $e->getMessage(), 'dish_id' => $id]);
            return respondError('Server error occurred', 500);
        }
    }

    public function restore($id)
    {
        try {
            $dish = Dish::withTrashed()->findOrFail($id);
            $dish->restore();

            return [
                'status' => true,
                'message' => 'Dish restored successfully',
                'data' => $dish
            ];
        } catch (\Exception $e) {
            Log::error('Dish Restoration Failed', ['error' => $e->getMessage(), 'dish_id' => $id]);
            return [
                'status' => false,
                'message' => 'Server error occurred',
                'data' => $e->getMessage(),
            ];
        }
    }

    private function validateDishData($data)
    {

        // dd($data);

        // \Log::debug('Data type:', ['type' => gettype($data)]);
        //   \Log::debug('Data content:', ['content' => $data]);

        $rules = [
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'category_id' => 'required|integer|exists:dish_categories,id',
            'cuisine_id' => 'required|integer|exists:cuisines,id',
            'item_code_id' => 'required|integer|exists:item_codes,id',
            'price' => 'required_if:has_sizes,0|nullable|numeric|min:0',
            'image' => 'nullable|image|max:5000',
            'is_active' => 'required|boolean',
            'has_sizes' => 'required|boolean',
            'has_addon' => 'required|boolean',
            'time' => 'nullable|integer|min:1',
            'time_cancelation' => 'nullable|integer|min:1',

            // Sizes validation
            'sizes' => 'required_if:has_sizes,1|array|min:1',
            'sizes.*.size_name_en' => 'required_if:has_sizes,1|string|max:255',
            'sizes.*.size_name_ar' => 'required_if:has_sizes,1|string|max:255',
            'sizes.*.price' => 'required_if:has_sizes,1|numeric|min:0',
            'sizes.*.recipes' => 'required_if:has_sizes,1|array|min:1',
            'sizes.*.recipes.*.recipe_id' => 'required_if:has_sizes,1|integer|exists:recipes,id',
            'sizes.*.recipes.*.quantity' => 'required_if:has_sizes,1|numeric|min:0.01',

            // Details validation (for dishes without sizes)
            'details' => 'required_if:has_sizes,0|array|min:1',
            'details.*.recipe_id' => 'required_if:has_sizes,0|integer|exists:recipes,id',
            'details.*.quantity' => 'required_if:has_sizes,0|numeric|min:0.01',

            // Addons validation
            'addon_categories' => 'required_if:has_addon,1|array|min:1',
            'addon_categories.*.addon_category_id' => 'required_if:has_addon,1|integer|exists:addon_categories,id',
            'addon_categories.*.min_addons' => 'nullable|integer|min:0',
            'addon_categories.*.max_addons' => 'nullable|integer|min:0|gte:addon_categories.*.min_addons',
            'addon_categories.*.addons' => 'required_if:has_addon,1|array|min:1',
            'addon_categories.*.addons.*.recipe_id' => 'required_if:has_addon,1|integer|exists:recipes,id',
            'addon_categories.*.addons.*.quantity' => 'nullable|integer|min:1',
            'addon_categories.*.addons.*.price' => 'nullable|numeric|min:0',

            // Branches
            'branches' => 'nullable|array',
        ];



        $messages = [
            'name_en.required' => 'English name is required.',
            'name_ar.required' => 'Arabic name is required.',
            'category_id.required' => 'Category is required.',
            'category_id.exists' => 'Selected category does not exist.',
            'cuisine_id.required' => 'Cuisine is required.',
            'cuisine_id.exists' => 'Selected cuisine does not exist.',
            'item_code_id.required' => 'Item code is required.',
            'item_code_id.exists' => 'Selected item code does not exist.',
            'price.required_if' => 'Price is required when dish has no sizes.',
            'has_sizes.required' => 'Has sizes field is required.',
            'has_addon.required' => 'Has addons field is required.',

            // Sizes messages
            'sizes.required_if' => 'Sizes are required when has sizes is enabled.',
            'sizes.min' => 'At least one size must be provided.',
            'sizes.*.size_name_en.required_if' => 'Size name (English) is required.',
            'sizes.*.size_name_ar.required_if' => 'Size name (Arabic) is required.',
            'sizes.*.price.required_if' => 'Size price is required.',
            'sizes.*.recipes.required_if' => 'Size recipes are required.',
            'sizes.*.recipes.min' => 'At least one recipe must be provided for each size.',
            'sizes.*.recipes.*.recipe_id.required_if' => 'Recipe is required.',
            'sizes.*.recipes.*.recipe_id.exists' => 'Selected recipe does not exist.',
            'sizes.*.recipes.*.quantity.required_if' => 'Recipe quantity is required.',
            'sizes.*.recipes.*.quantity.min' => 'Recipe quantity must be at least 0.01.',

            // Details messages
            'details.required_if' => 'Dish details are required when dish has no sizes.',
            'details.min' => 'At least one dish detail must be provided.',
            'details.*.recipe_id.required_if' => 'Recipe is required.',
            'details.*.recipe_id.exists' => 'Selected recipe does not exist.',
            'details.*.quantity.required_if' => 'Recipe quantity is required.',
            'details.*.quantity.min' => 'Recipe quantity must be at least 0.01.',

            // Addons messages
            'addon_categories.required_if' => 'Addon categories are required when has addons is enabled.',
            'addon_categories.min' => 'At least one addon category must be provided.',
            'addon_categories.*.addon_category_id.required_if' => 'Addon category is required.',
            'addon_categories.*.addon_category_id.exists' => 'Selected addon category does not exist.',
            'addon_categories.*.max_addons.gte' => 'Maximum addons must be greater than or equal to minimum addons.',
            'addon_categories.*.addons.required_if' => 'Addons are required.',
            'addon_categories.*.addons.min' => 'At least one addon must be provided for each category.',
            'addon_categories.*.addons.*.recipe_id.required_if' => 'Addon recipe is required.',
            'addon_categories.*.addons.*.recipe_id.exists' => 'Selected addon recipe does not exist.',
            'addon_categories.*.addons.*.quantity.integer' => 'Addon quantity must be an integer.',
            'addon_categories.*.addons.*.quantity.min' => 'Addon quantity must be at least 1.',
            'addon_categories.*.addons.*.price.numeric' => 'Addon price must be numeric.',
            'addon_categories.*.addons.*.price.min' => 'Addon price must be at least 0.',
        ];



        $validator = Validator::make($data, $rules, $messages);

        // Custom validation for duplicate recipes
        $validator->after(function ($validator) use ($data) {
            // Check for duplicate recipes in sizes
            if (!empty($data['has_sizes']) && !empty($data['sizes'])) {
                foreach ($data['sizes'] as $sizeIndex => $size) {
                    $recipeIds = [];
                    foreach ($size['recipes'] ?? [] as $recipeIndex => $recipe) {
                        if (isset($recipe['recipe_id'])) {
                            if (in_array($recipe['recipe_id'], $recipeIds)) {
                                $validator->errors()->add(
                                    "sizes.$sizeIndex.recipes.$recipeIndex.recipe_id",
                                    'This recipe is already used in this size.'
                                );
                            } else {
                                $recipeIds[] = $recipe['recipe_id'];
                            }
                        }
                    }
                }
            }

            // Check for duplicate recipes in details
            if (empty($data['has_sizes']) && !empty($data['details'])) {
                $recipeIds = [];
                foreach ($data['details'] as $detailIndex => $detail) {
                    if (isset($detail['recipe_id'])) {
                        if (in_array($detail['recipe_id'], $recipeIds)) {
                            $validator->errors()->add(
                                "details.$detailIndex.recipe_id",
                                'This recipe is already used in dish details.'
                            );
                        } else {
                            $recipeIds[] = $detail['recipe_id'];
                        }
                    }
                }
            }

            // Check for duplicate addons within each category
            if (!empty($data['has_addon']) && !empty($data['addon_categories'])) {
                foreach ($data['addon_categories'] as $categoryIndex => $category) {
                    $recipeIds = [];
                    foreach ($category['addons'] ?? [] as $addonIndex => $addon) {
                        if (isset($addon['recipe_id'])) {
                            if (in_array($addon['recipe_id'], $recipeIds)) {
                                $validator->errors()->add(
                                    "addon_categories.$categoryIndex.addons.$addonIndex.recipe_id",
                                    'This addon recipe is already used in this category.'
                                );
                            } else {
                                $recipeIds[] = $addon['recipe_id'];
                            }
                        }
                    }
                }
            }
        });

        return $validator->validate();
    }

    private function createDishSizes($dish, $sizes, $defaultSizeIndex = null)
    {
        foreach ($sizes as $index => $sizeData) {
            $dishSize = DishSize::create([
                'dish_id' => $dish->id,
                'size_name_en' => $sizeData['size_name_en'],
                'size_name_ar' => $sizeData['size_name_ar'],
                'price' => $sizeData['price'],
                'default_size' => ($defaultSizeIndex !== null && $defaultSizeIndex == $index),
            ]);

            // Create recipes for this size
            if (isset($sizeData['recipes'])) {
                foreach ($sizeData['recipes'] as $recipe) {
                    DishDetail::create([
                        'dish_id' => $dish->id,
                        'dish_size_id' => $dishSize->id,
                        'recipe_id' => $recipe['recipe_id'] ?? null,
                        'quantity' => $recipe['quantity'] ?? 1,
                    ]);
                }
            }
        }
    }

    private function createDishDetails($dish, $details)
    {
        foreach ($details as $detail) {
            DishDetail::create([
                'dish_id' => $dish->id,
                'dish_size_id' => null,
                'recipe_id' => $detail['recipe_id'] ?? null,
                'quantity' => $detail['quantity'] ?? 1,
            ]);
        }
    }

    private function createDishAddons($dish, $addonCategories)
    {
        foreach ($addonCategories as $categoryData) {
            foreach ($categoryData['addons'] as $addonData) {
                DishAddon::create([
                    'dish_id' => $dish->id,
                    'addon_id' => $addonData['recipe_id'] ?? null, // Store recipe_id in addon_id field
                    'quantity' => $addonData['quantity'] ?? 1,
                    'price' => $addonData['price'] ?? 0,
                    'addon_category_id' => $categoryData['addon_category_id'],
                    'min_addons' => $categoryData['min_addons'] ?? 0,
                    'max_addons' => $categoryData['max_addons'] ?? 0,
                ]);
            }
        }
    }

    private function assignDishToBranches($dish, $branches)
    {
        $userId = auth('admin')->id();

        if (in_array('all', $branches)) {
            $allBranchIds = Branch::pluck('id')->toArray();
            $this->addDishToBranches($dish, $allBranchIds, $userId);
        } else {
            $this->addDishToBranches($dish, $branches, $userId);
        }
    }

    private function addDishToBranches($dish, $branchIds, $userId)
    {
        foreach ($branchIds as $branchId) {
            $userId  = authActionSave()['by'];
            // First ensure branch menu category exists
            $branchMenuCategory = \App\Models\BranchMenuCategory::updateOrCreate(
                ['dish_category_id' => $dish->category_id, 'branch_id' => $branchId],
                ['is_active' => 1, 'created_by' => $userId, 'created_type' => authActionSave()['type']]
            );

            // Then add the dish to branch menu
            $existingRecord = \App\Models\BranchMenu::where([
                'dish_id' => $dish->id,
                'branch_id' => $branchId
            ])->first();

            if ($existingRecord) {
                $existingRecord->update([
                    'is_active' => $dish->is_active,
                    'modified_by' => $userId,
                ]);
            } else {
                \App\Models\BranchMenu::create([
                    'dish_id' => $dish->id,
                    'branch_id' => $branchId,
                    'branch_menu_category_id' => $branchMenuCategory->id,
                    'price' => $dish->price,
                    'is_product' => 0,
                    'is_active' => 1,
                    'created_by' => $userId
                ]);
            }
        }
    }

    private function updateDishSizes($dish, $sizes, $defaultSizeIndex = null)
    {
        // Get existing size IDs that should remain
        $sizeIdsToKeep = [];
        foreach ($sizes as $index => $sizeData) {
            if (isset($sizeData['id'])) {
                $sizeIdsToKeep[] = $sizeData['id'];
            }
        }

        // Delete sizes that are not in the request
        $dish->sizes()->whereNotIn('id', $sizeIdsToKeep)->delete();

        foreach ($sizes as $index => $sizeData) {
            $dishSize = DishSize::updateOrCreate(
                [
                    'dish_id' => $dish->id,
                    'id' => $sizeData['id'] ?? null,
                ],
                [
                    'size_name_en' => $sizeData['size_name_en'],
                    'size_name_ar' => $sizeData['size_name_ar'],
                    'price' => $sizeData['price'],
                    'default_size' => ($defaultSizeIndex !== null && $defaultSizeIndex == $index),
                ]
            );

            // Update recipes for this size
            if (isset($sizeData['recipes'])) {
                // Delete existing recipes for this size
                DishDetail::where('dish_id', $dish->id)
                    ->where('dish_size_id', $dishSize->id)
                    ->delete();

                // Add new recipes
                foreach ($sizeData['recipes'] as $recipe) {
                    DishDetail::create([
                        'dish_id' => $dish->id,
                        'dish_size_id' => $dishSize->id,
                        'recipe_id' => $recipe['recipe_id'] ?? null,
                        'quantity' => $recipe['quantity'] ?? 1,
                    ]);
                }
            }
        }
    }

    private function updateDishDetails($dish, $details)
    {
        // Get existing detail IDs that should remain
        $detailIdsToKeep = [];
        foreach ($details as $detailData) {
            if (isset($detailData['id'])) {
                $detailIdsToKeep[] = $detailData['id'];
            }
        }

        // Delete details that are not in the request (and not associated with sizes)
        $dish->details()->whereNull('dish_size_id')->whereNotIn('id', $detailIdsToKeep)->delete();

        foreach ($details as $detailData) {
            DishDetail::updateOrCreate(
                [
                    'dish_id' => $dish->id,
                    'id' => $detailData['id'] ?? null,
                ],
                [
                    'dish_size_id' => null,
                    'recipe_id' => $detailData['recipe_id'] ?? null,
                    'quantity' => $detailData['quantity'] ?? 1,
                ]
            );
        }
    }

    private function updateDishAddons($dish, $addonCategories)
    {
        // Get all existing addon IDs that should remain
        $addonIdsToKeep = [];
        foreach ($addonCategories as $categoryData) {
            foreach ($categoryData['addons'] as $addonData) {
                if (isset($addonData['id'])) {
                    $addonIdsToKeep[] = $addonData['id'];
                }
            }
        }

        // Delete addons that are not in the request
        $dish->dishAddonsDetails()->whereNotIn('id', $addonIdsToKeep)->delete();

        foreach ($addonCategories as $categoryData) {
            foreach ($categoryData['addons'] as $addonData) {
                DishAddon::updateOrCreate(
                    [
                        'dish_id' => $dish->id,
                        'id' => $addonData['id'] ?? null,
                    ],
                    [
                        'addon_id' => $addonData['recipe_id'] ?? null, // Store recipe_id in addon_id field
                        'quantity' => $addonData['quantity'] ?? 1,
                        'price' => $addonData['price'] ?? 0,
                        'addon_category_id' => $categoryData['addon_category_id'],
                        'min_addons' => $categoryData['min_addons'] ?? 0,
                        'max_addons' => $categoryData['max_addons'] ?? 0,
                    ]
                );
            }
        }
    }
    public function saveIngredient(Request $request)
    {
        $lang = app()->getLocale();

        $messages = [
            'dish_id.required' => $lang == 'en' ? 'Dish ID is required.' : 'معرف الطبق مطلوب.',
            'dish_id.exists' => $lang == 'en' ? 'The selected dish does not exist.' : 'الطبق المحدد غير موجود.',
            'items.required' => $lang == 'en' ? 'Items are required.' : 'العناصر مطلوبة.',
            'items.min' => $lang == 'en' ? 'At least one item is required.' : 'عنصر واحد على الأقل مطلوب.',
            'items.*.recipe_title.required_with' => $lang == 'en' ? 'Recipe title is required when recipe steps are provided.' : 'عنوان الوصفة مطلوب عند توفير خطوات الوصفة.',
            'items.*.recipe_steps.required_with' => $lang == 'en' ? 'Recipe steps are required when recipe title is provided.' : 'خطوات الوصفة مطلوبة عند توفير عنوان الوصفة.',
            'items.*.recipe_title.string' => $lang == 'en' ? 'Recipe title must be a string.' : 'عنوان الوصفة يجب أن يكون نص.',
            'items.*.recipe_steps.string' => $lang == 'en' ? 'Recipe steps must be a string.' : 'خطوات الوصفة يجب أن تكون نص.',
            'items.*.note_title.required_with' => $lang == 'en' ? 'Note title is required when note steps are provided.' : 'عنوان الملاحظة مطلوب عند توفير خطوات الملاحظة.',
            'items.*.note_steps.required_with' => $lang == 'en' ? 'Note steps are required when note title is provided.' : 'خطوات الملاحظة مطلوبة عند توفير عنوان الملاحظة.',
            'items.*.note_title.string' => $lang == 'en' ? 'Note title must be a string.' : 'عنوان الملاحظة يجب أن يكون نص.',
            'items.*.note_steps.string' => $lang == 'en' ? 'Note steps must be a string.' : 'خطوات الملاحظة يجب أن تكون نص.',
        ];

        $validator = Validator::make($request->all(), [
            'dish_id' => 'required|integer|exists:dishes,id',
            'items' => 'required|array|min:1',
            'items.*.recipe_title' => 'nullable|string|required_with:items.*.recipe_steps',
            'items.*.recipe_steps' => 'nullable|string|required_with:items.*.recipe_title',
            'items.*.note_title' => 'nullable|string|required_with:items.*.note_steps',
            'items.*.note_steps' => 'nullable|string|required_with:items.*.note_title',
        ], $messages);

        // Custom validation to ensure each item has at least recipe or note data
        $validator->after(function ($validator) use ($request, $lang) {
            if (isset($request->items)) {
                foreach ($request->items as $index => $item) {
                    $hasRecipe = !empty($item['recipe_title']) && !empty($item['recipe_steps']);
                    $hasNote = !empty($item['note_title']) && !empty($item['note_steps']);

                    if (!$hasRecipe && !$hasNote) {
                        $message = $lang == 'en'
                            ? 'Each item must have either recipe data (title + steps) or note data (title + steps) or both.'
                            : 'كل عنصر يجب أن يحتوي على بيانات الوصفة (العنوان + الخطوات) أو بيانات الملاحظة (العنوان + الخطوات) أو كليهما.';

                        $validator->errors()->add("items.$index", $message);
                    }
                }
            }
        });        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }

        try {
            //delete existing records for this dish
            DishIngredientStep::where('dish_id', $request->dish_id)->delete();

            // Create one record for each item
            foreach ($request->items as $item) {
                DishIngredientStep::create([
                    'dish_id' => $request->dish_id,
                    'recipe_title' => $item['recipe_title'] ?? null,
                    'recipe_steps' => $item['recipe_steps'] ?? null, // Store as plain text
                    'note_title' => $item['note_title'] ?? null,
                    'note_steps' => $item['note_steps'] ?? null, // Store as plain text
                ]);
            }

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            Log::error('Error saving dish ingredients', [
                'error' => $e->getMessage(),
                'dish_id' => $request->dish_id
            ]);
            return RespondWithBadRequestWithData(['error' => 'Failed to save dish ingredients']);
        }
    }
}
