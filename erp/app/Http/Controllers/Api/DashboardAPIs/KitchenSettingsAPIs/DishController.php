<?php

namespace App\Http\Controllers\Api\DashboardAPIs\KitchenSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Services\KitchenServices\DishService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DishController extends Controller
{
    protected $dishService;

    public function __construct(DishService $dishService)
    {
        $this->dishService = $dishService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $dishes = $this->dishService->index();

        $response = paginateOrGetAll($dishes, $request);
        $response['data'] = collect($response['data'])->map(function ($dish) {
            return $dish->makeHidden(['name_site', 'description', 'description_site']);
        })->all();

        return ResponseWithSuccessDataPaginated($lang, $response, 1);
    }

    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $result = $this->dishService->show($id);
            if (!$result) {
                $message = $lang == 'en' ? 'Dish not found' : 'الطبق غير موجود';
                return respondError($message, 404);
            }
            return ResponseWithSuccessData($lang, $result, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching dish category: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        // Basic validation - detailed validation is handled by the service
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:dish_categories,id',
            'cuisine_id' => 'required|integer|exists:cuisines,id',
            'item_code_id' => 'required|integer|exists:item_codes,id',
            'is_active' => 'required|boolean',
            'has_sizes' => 'required|boolean',
            'has_addon' => 'required|boolean',
        ]);

        $data = $request->all();
        $dish = $this->dishService->store($data);

        // Handle service response format
        if (isset($dish['status']) && !$dish['status']) {
            return respondError($dish['message'], 400, $dish['data']);
        }

        return ResponseWithSuccessData($lang, $dish, 1);
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $dish = Dish::find($id);
        if (!$dish) {
            $message = $lang == 'en' ? 'Dish not found' : 'الطبق غير موجود';
            return respondError($message, 404);
        }
        // Basic validation - detailed validation is handled by the service
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:dish_categories,id',
            'cuisine_id' => 'required|integer|exists:cuisines,id',
            'item_code_id' => 'required|integer|exists:item_codes,id',
            'is_active' => 'required|boolean',
            'has_sizes' => 'required|boolean',
            'has_addon' => 'required|boolean',
        ]);

        $data = $request->all();
        $dish = $this->dishService->update($data, $id);
        if ($dish instanceof \Illuminate\Http\JsonResponse) {
            return $dish;
        }

        return ResponseWithSuccessData($lang, $dish, 1);
    }

    public function destroy($id)
    {
        $lang = app()->getLocale();
        $result = $this->dishService->delete($id);

        // Handle JsonResponse from respondError()
        if ($result instanceof \Illuminate\Http\JsonResponse) {
            return $result;
        }

        // Handle array response for success
        if (isset($result['status']) && !$result['status']) {
            return respondError($result['message'], 400, $result['data']);
        }
    }

    public function restore($id)
    {
        $result = $this->dishService->restore($id);

        // Handle service response format
        if (isset($result['status']) && !$result['status']) {
            return respondError($result['message'], 400, $result['data']);
        }

        return response()->json($result);
    }

    public function saveIngredient(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $result = $this->dishService->saveIngredient($request);

            if ($result instanceof \Illuminate\Http\JsonResponse) {
                return $result;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Error saving dish ingredients: ' . $e->getMessage());
            $lang = $request->header('lang', 'ar');
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
