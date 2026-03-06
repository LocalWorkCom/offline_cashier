<?php

namespace App\Services\KitchenServices;

use App\Models\DishCategory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DishCategoryService
{
    public function index()
    {

        return DishCategory::with(['parent', 'children'])->where('is_active', 1);
    }

    public function show($id)
    {
        return DishCategory::with(['parent', 'children'])->find($id);
    }

    public function store($request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|filled|string|max:255',
            'name_en' => 'required|filled|string|max:255',
            'description_ar' => 'required|filled|string',
            'description_en' => 'required|filled|string',
            'is_active' => 'required|boolean',
            'parent_id' => 'nullable|exists:dish_categories,id',
            'image_path' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }

        DB::beginTransaction();
        try {
            $data = $request->except('image_path');

            $data['created_by'] = authActionSave()['by'];
            $data['created_type'] =  authActionSave()['type'];

            $category = DishCategory::create($data);

            // Handle image upload if provided
            if ($request->hasFile('image_path')) {
                $image_path = $request->file('image_path');
                UploadFile('images/dish_category', 'image_path', $category, $image_path);
            }
            DB::commit();
            return DishCategory::with(['parent', 'children'])->find($category->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Dish Category Creation Failed', ['error' => $e->getMessage()]);
            return respondError('Creation Failed.', 500, ['error' => $e->getMessage()]);
        }
    }


    public function update($id, $request)
    {
        $lang = app()->getLocale();

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|filled|string|max:255',
            'name_en' => 'required|filled|string|max:255',
            'description_ar' => 'required|filled|string',
            'description_en' => 'required|filled|string',
            'is_active' => 'required|boolean',
            'parent_id' => 'nullable|exists:dish_categories,id',
            'image_path' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return respondError('Validation Error.', 400, $validator->errors());
        }

        DB::beginTransaction();
        try {
            $category = DishCategory::find($id);
            if (!$category) {
                return respondErrorData(
                    $lang == 'en' ? 'Category not found.' : 'الفئة غير موجودة.',
                    404
                );
            }
            $updateData = $request->except('image_path');

            $updateData['modified_by'] = authActionSave()['by'];
            $updateData['modified_type'] =  authActionSave()['type'];

            $category->update($updateData);

            // Check if a new image is uploaded
            if ($request->hasFile('image_path')) {
                DeleteFile('images/dish_category', $category->image_path);
                $image_path = $request->file('image_path');
                UploadFile('images/dish_category', 'image_path', $category, $image_path);
            }
            DB::commit();
            return $category;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Dish Category Update Failed', ['error' => $e->getMessage()]);
            return respondError('Update Failed.', 500, ['error' => $e->getMessage()]);
        }
    }
    public function delete($id)
    {
        $lang = app()->getLocale();

        $category = DishCategory::find($id);

        if (!$category) {
            return respondErrorData(
                $lang == 'en' ? 'Category not found.' : 'الفئة غير موجودة.',
                404
            );
        }
        if (DishCategory::where('parent_id', $id)->exists()) {
            return respondErrorData(
                $lang == 'en' ? 'The category is referenced as a parent in another category and cannot be deleted.' : 'الفئة مرجعة كوالد في فئة أخرى ولا يمكن حذفها.',
                400
            );
        }
        if ($category->dishes()->whereNull('deleted_at')->exists()) {
            return respondErrorData(
                $lang == 'en' ? 'The category is referenced with dishes and cannot be deleted.' : 'الفئة مرجعة مع الأطباق ولا يمكن حذفها.',
                400
            );
        }

        // Soft delete the category
        $category->update(['deleted_by' => authActionSave()['by'], 'deleted_type' => authActionSave()['type']]);
        $category->save();
        $category->delete();

        $lang = app()->getLocale();
        $message = $lang == 'en' ? 'Category deleted successfully.' : 'تم حذف الفئة بنجاح.';

        return RespondWithSuccessRequest($lang, 1);
    }


    public function restore($id)
    {
        $category = DishCategory::withTrashed()->findOrFail($id);

        $category->restore();

        return true;
    }
}
