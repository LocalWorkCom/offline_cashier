<?php

namespace App\Http\Controllers\Api\DashboardAPIs\KitchenSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Services\KitchenServices\DishCategoryService;
use App\Traits\DishCategoryTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DishCategoryController extends Controller
{
    use DishCategoryTrait;
    protected $dishCategoryService;

    public function __construct(DishCategoryService $dishCategoryService)
    {
        $this->dishCategoryService = $dishCategoryService;
    }

    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');

            $categories = $this->dishCategoryService->index();

            $response = paginateOrGetAll($categories, $request, ['image_path'], ['image']);
            $response['data'] = $response['data']->map(function($dish) use($lang) {
                $dish->is_active = $dish->is_active ? ($lang == 'en' ? 'Active' : 'نشط') : ($lang == 'en' ? 'Not Active' : 'غير نشط');
                return $dish;
            });

            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching dish categories: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $category = $this->dishCategoryService->show($id);
        if (!$category) {
            $message = $lang == 'en' ? 'Category not found' : 'الفئة غير موجودة';
            return respondError($message, 404);
        }
        // dd($category);
        $responseDate = [
            'id' => $category->id,
            'name' => $category->name,
            'name_ar' => $category->name_ar,
            'name_en' => $category->name_en,
            'description' => $category->description,
            'description_ar' => $category->description_ar,
            'description_en' => $category->description_en,
            'image' => $category->image,
            'is_active' => $category->is_active,
            'parent_id' => $category->parent_id,
            'parent' => $category->parent ? [
                'id' => $category->parent->id,
                'name' => $category->parent->name,
                'name_ar' => $category->parent->name_ar,
                'name_en' => $category->parent->name_en,
            ] : null,
        ];
        return ResponseWithSuccessData($lang, $responseDate, 1);
    }
    public function store(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');

            $result = $this->dishCategoryService->store($request);

            if ($result instanceof \Illuminate\Http\JsonResponse) {
                return $result;
            }
            return ResponseWithSuccessData($lang, $result, 1);
        } catch (\Exception $e) {
            $lang = $request->header('lang', 'ar');
            Log::error('Error creating dish category: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');

            $result = $this->dishCategoryService->update($id, $request);

            if ($result instanceof \Illuminate\Http\JsonResponse) {
                return $result;
            }

            return ResponseWithSuccessData($lang, $result, 1);
        } catch (\Exception $e) {
            Log::error('Error updating dish category: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');

            $result = $this->dishCategoryService->delete($id);

            if ($result instanceof \Illuminate\Http\JsonResponse) {
                return $result;
            }
        } catch (\Exception $e) {
            $lang = $request->header('lang', 'ar');
            Log::error('Error deleting dish category: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function restore(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $category = $this->dishCategoryService->restore($id);

            return ResponseWithSuccessData($lang, $category, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring dish category: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
