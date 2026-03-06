<?php

namespace App\Http\Controllers\Api\DashboardAPIs\KitchenSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecipeResource;
use App\Services\KitchenServices\AddonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AddonController extends Controller
{
    protected $addonService;
    protected $lang;  // Set to true or false based on your need
    protected  $visible = ['name_ar', 'name_en', 'description_ar', 'description_en','item_code_id'];

    public function __construct(AddonService $addonService, Request $request)
    {
        $this->addonService = $addonService;
        $this->lang = $request->header('lang', 'ar');
    }


    public function index(Request $request)
    {
        $withTrashed = $request->query('withTrashed', false);

        $query = $this->addonService->index($withTrashed);
        $fields = ['name', 'name_site', 'description_site', 'created_at', 'updated_at', 'deleted_at','item_code_id'];
        $response = paginateOrGetAll($query, $request, $fields, $this->visible);
        $resourceData = RecipeResource::collection($response['data']);

        return ResponseWithSuccessDataPaginated(
            $this->lang,
            [
                'data' => $resourceData,
                'meta' => $response['meta']
            ],
            1
        );
 }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $addon = $this->addonService->show($id)
                ->makeHidden(['name', 'name_site', 'description_site','item_code_id'])->makeVisible($this->visible);

            return ResponseWithSuccessData($lang, new RecipeResource($addon), 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'الوصفة غير موجودة' : 'Addon not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching addon: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function store(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');

            // Validate the request
            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'description_ar' => 'required|string',
                'description_en' => 'required|string',
                'is_active' => 'required|boolean',
                'time' => 'nullable|integer',
                'ingredients' => 'required|array',
                'ingredients.*.product_brand_id' => 'required|exists:product_brands,id',
                'ingredients.*.quantity' => 'required|numeric|min:0',
                'ingredients.*.loss_percent' => 'nullable|numeric|min:0|max:100',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                // 'item_code_id' => 'required|integer|exists:item_codes,id',
            ]);

            if ($validator->fails()) {
                return respondError(
                    $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                    400,
                    $validator->errors()
                );
            }

            $data = $validator->validated();

            // ✅ Pass files correctly
            $images = $request->file('images', []);

            $recipe = $this->addonService->store($data, $images);

            return ResponseWithSuccessData($lang, new RecipeResource($recipe), 1);
        } catch (\Exception $e) {
            Log::error('Error creating recipe: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $validator =  Validator::make($request->all(), [
                'name_ar' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'description_ar' => 'required|string',
                'description_en' => 'required|string',
                'is_active' => 'required|boolean',
                'time' => 'nullable|integer',
                'ingredients' => 'required|array',
                'ingredients.*.product_brand_id' => 'required|exists:product_brands,id',
                'ingredients.*.quantity' => 'required|numeric|min:0',
                'ingredients.*.loss_percent' => 'nullable|numeric|min:0|max:100',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            if ($validator->fails()) {
                return respondError(
                    $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                    400,
                    $validator->errors()
                );
            }
            $validatedData = $validator->validated();

            $recipe = $this->addonService->update($id, $validatedData, $request->file('images'));
            if ($recipe instanceof \Illuminate\Http\JsonResponse) {
                return $recipe;
            }
            return ResponseWithSuccessData($lang, new RecipeResource($recipe), 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'الوصفة غير موجودة' : 'Addon not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching addon: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $response = $this->addonService->delete($request, $id);

            // If response is a JSON error (bad request, etc.), return it directly
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                return $response;
            }

            // Return success response
            $message = $this->lang === 'ar' ? 'تم حذف الفئة بنجاح' : 'Category deleted successfully';
            return ResponseWithSuccessData($this->lang, $message, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'الوصفة غير موجودة' : 'Addon not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching addon: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function restore($id)
    {
        $addonCategory = $this->addonService->restore($id);
        return response()->json($addonCategory);
    }
}
