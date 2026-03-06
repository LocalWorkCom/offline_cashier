<?php

namespace App\Services\KitchenServices;

use App\Models\Cuisine;
use App\Models\DishCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CuisineService
{
    public function index($withTrashed)
    {
        return $withTrashed
            ? Cuisine::withTrashed()
            : Cuisine::query();
    }

    public function show($id)
    {
        return Cuisine::findOrFail($id);
    }

    public function store($data, $image = null)
    {
        try {
            // if ($image) {
            //     $destinationPath = 'images/cuisines';

            //     $imageName = time() . '_' . $image->getClientOriginalName();

            //     $image->move(public_path($destinationPath), $imageName);

            //     $data['image_path'] = $destinationPath . '/' . $imageName;
            // }
            $data['created_by'] = authActionSave()['by'];
            $data['created_by_type'] =  authActionSave()['type'];
            $Cuisine = Cuisine::create($data);
            if (isset($image)) {
                UploadFile('images/cuisines', 'image_path', $Cuisine, $image);
            }

            return $Cuisine;
        } catch (\Exception $e) {
            Log::error('Error storing cuisine', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update($id, $data, $image = null)
    {
        $cuisine = Cuisine::findOrFail($id);

        $data['modified_by'] = authActionSave()['by'];
        $data['modified_by_type'] =  authActionSave()['type'];

        $cuisine->update($data);
        if (isset($image)) {

            UploadFile('images/cuisines', 'image_path', $cuisine, $image);
        }
        return $cuisine;
    }


    public function delete($id)
    {
        $lang = app()->getLocale();
        // Find the dish by ID or return a 404 response if not found
        $cuisine = Cuisine::findOrFail($id);

        // Check if there are any relations preventing deletion
        // if ($cuisine->dishes()->exists()) {
        //     return CustomRespondWithBadRequest(__('cuisines.The cuisines have relation with dish'));
        // }

        $cuisine->update(['deleted_by' => authActionSave()['by'], 'deleted_by_type' => authActionSave()['type']]);
        $cuisine->delete();

        // Return a success response
        return RespondWithSuccessRequest($lang, 1);
    }


    public function restore($id)
    {
        $cuisine = Cuisine::withTrashed()->findOrFail($id);
        $cuisine->restore();

        return $cuisine;
    }

    public function assignCuisineCategory(Request $request, $id)
    {
        $cuisine = Cuisine::findOrFail($id);
        $userId = auth('employee')->id();

        // Get the currently assigned dish categories
        $currentCategories = $cuisine->dishCategories()->pluck('dish_category_id')->toArray();

        // Get the requested categories
        $requestedCategories = $request->input('dish_categories', []);
        // Find categories to be removed
        $categoriesToRemove = array_diff($currentCategories, $requestedCategories);

        // Check if any of the categories to be removed are still in use
        $usedCategories = DB::table('chef_cuisine_categories')
            ->whereIn('cuisine_category_id', function ($query) use ($categoriesToRemove, $id) {
                $query->select('id')
                    ->from('cuisines_categories')
                    ->where('cuisine_id', $id)
                    ->whereIn('dish_category_id', $categoriesToRemove);
            })
            ->whereNull('deleted_at')
            ->pluck('cuisine_category_id')
            ->toArray();

        if (!empty($usedCategories)) {
            // Get the dish category names for the error message
            $usedDishCategories = DB::table('dish_categories')
                ->whereIn('id', function ($query) use ($usedCategories, $id) {
                    $query->select('dish_category_id')
                        ->from('cuisines_categories')
                        ->where('cuisine_id', $id)
                        ->whereIn('id', $usedCategories);
                })
                ->pluck(app()->getLocale() == 'en' ? 'name_en' : 'name_ar')
                ->toArray();

            return back()->withErrors([
                'dish_categories' => __('validation.this category are still assigned to chefs and cannot be removed')
            ]);
        }

        // Proceed with the sync if no conflicts
        $syncData = [];
        foreach ($requestedCategories as $dishCategoryId) {
            $syncData[$dishCategoryId] = [
                'created_by' => $userId,
                'modified_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $cuisine->dishCategories()->sync($syncData);

        return $cuisine;
    }
}
