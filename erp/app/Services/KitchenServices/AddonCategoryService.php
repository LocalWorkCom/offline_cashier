<?php

namespace App\Services\KitchenServices;

use Illuminate\Http\Request;
use App\Models\AddonCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AddonCategoryService
{
    public function index($withTrashed = false)
    {
        return $withTrashed
            ? AddonCategory::withTrashed()
            : AddonCategory::query()
            ->whereNull('deleted_at');
    }

    public function show($id)
    {
        return AddonCategory::findOrFail($id);
    }

    public function store($data)
    {
        try {
            $validatedData = $this->validateData($data);

            // If validateData returns a JsonResponse (custom error), return it directly
            if ($validatedData instanceof \Illuminate\Http\JsonResponse) {
                return $validatedData;
            }
            $validatedData['created_by'] = authActionSave()['by'];
            $validatedData['created_by_type'] = authActionSave()['type'];
            // dd($validatedData);
            return AddonCategory::create($validatedData);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return using your custom validation error handler
            return respondError(__('Validation failed'), 422, $e->errors());
        } catch (\Exception $e) {
            // Log other errors
            Log::error('Error storing addon category: ' . $e->getMessage());
            return respondError(__('Something went wrong while saving the data.'), 500);
        }
    }


    public function update($id, $data)
    {
        try {
            $addonCategory = AddonCategory::findOrFail($id);

            $validatedData = $this->validateData($data);
            if ($validatedData instanceof \Illuminate\Http\JsonResponse) {
                return $validatedData;
            }

            $addonCategory->update($validatedData);
            return $addonCategory;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return  RespondWithBadRequestNotExist();
        }
    }


    public function delete($id, $lang)
    {
        try {
            // Find the addon category or fail
            $addonCategory = AddonCategory::findOrFail($id);

            // Check for relational integrity (e.g., related dish addons)
            if ($addonCategory->dishAddons()->exists()) {
                return  RespondWithBadRequestNotExist();
                // return CustomRespondWithBadRequest(
                //     __('addon_categories.The addonCategory have relation with dish')
                // );
            }

            // Track the deleter from either guard
            $addonCategory->update([
                'deleted_by' => authActionSave()['by'],
                'deleted_by_type' => authActionSave()['type']
            ]);

            // Perform soft delete
            $addonCategory->delete();

            // Success response
            return RespondWithSuccessRequest($lang, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return  RespondWithBadRequestNotExist();
        } catch (\Exception $e) {
            Log::error('Error deleting addon category: ' . $e->getMessage());
            return respondError(__('Something went wrong'), 500);
        }
    }



    public function restore($id)
    {
        $addonCategory = AddonCategory::withTrashed()->findOrFail($id);
        $addonCategory->restore();
        return $addonCategory;
    }

    private function validateData($data)
    {
        $validator = Validator::make($data, [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'required|string',
            'description_en' => 'required|string',
        ]);

        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }

        return $validator->validated(); // Returns valid array
    }
}
