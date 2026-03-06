<?php

namespace App\Http\Controllers\Api\DashboardAPIs\KitchenSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecipeResource;
use Illuminate\Http\Request;
use App\Services\KitchenServices\RecipeService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RecipeController extends Controller
{
    protected $recipeService;
    protected $fields = ['name_site', 'name', 'description', 'description_site', 'created_by_type', 'modified_by_type', 'deleted_by_type','item_code_id'];
    protected $fields_visible = ['name_ar', 'name_en', 'description_ar', 'description_en'];

    public function __construct(RecipeService $recipeService)
    {
        $this->recipeService = $recipeService;
    }

    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $withTrashed = $request->query('withTrashed', false);
            $query = $this->recipeService->index($withTrashed);
            $response = paginateOrGetAll($query, $request, $this->fields, $this->fields_visible);

            $resourceData = RecipeResource::collection($response['data']);

            return ResponseWithSuccessDataPaginated(
                $lang,
                [
                    'data' => $resourceData,
                    'meta' => $response['meta']
                ],
                1
            );
        } catch (\Exception $e) {
            Log::error('Error fetching recipes: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }



    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $recipe = $this->recipeService->show($id)
                ->makeHidden($this->fields)->makeVisible($this->fields_visible);

            // $recipeArray = $recipe->toArray();
            // $recipeArray['images'] = $recipe->images->makeHidden(['created_at', 'updated_at'])->map(function ($image) {
            //     return [
            //         'id' => $image['id'],
            //         'recipe_id' => $image['recipe_id'],
            //         'image' => $image['image_path'],
            //     ];
            // })->values();

            $recipe->is_active = $recipe->is_active == 1 ? ($lang === 'ar' ? 'نشط' : 'Active') : ($lang === 'ar' ? 'غير نشط' : 'Inactive');

            return ResponseWithSuccessData($lang, new RecipeResource($recipe), 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'الوصفة غير موجودة' : 'recipe not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching recipe: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function itemCodeList(Request $request)
    {
        //  try {
        $lang = $request->header('lang', 'ar');
        $itemCodes = $this->recipeService->itemCodeList()->makeHidden(['created_at', 'updated_at', 'activeTo', 'activeFrom', 'requestReason']);
        return ResponseWithSuccessData($lang, $itemCodes, 1);
    }

    public function store(Request $request)
    {
        // try {
            // Validate the request 
            $lang = $request->header('lang', 'ar');
            app()->setLocale($lang);
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
                // 'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                // 'item_code_id' => 'required|integer|exists:item_codes,id',
                // 'type' => 'required|integer|in:1,2',

            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $images = $request->file('images');
            if ($images && !is_array($images)) {
                $images = [$images]; // convert single file to array
            }

            $lang = $request->header('lang', 'ar');
            $data = $request->all();
            $recipe = $this->recipeService->store($data, $images);

            return ResponseWithSuccessData($lang, new RecipeResource($recipe), 1);
        // } catch (\Illuminate\Validation\ValidationException $e) {
        //     return RespondWithBadRequestWithData($e->errors());
        // } catch (\Exception $e) {
        //     Log::error('Error creating recipe: ' . $e->getMessage());
        //     return RespondWithBadRequestData($lang, 2);
        // }
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $data = array_merge($request->all(), ['id' => $id]);

            $validator = Validator::make($data, [
                'id' => [
                    'required',
                    'integer',
                    Rule::exists('recipes', 'id')
                        ->whereNull('deleted_at')
                        ->where('type', 1),
                ],
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
                // 'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                // 'item_code_id' => 'required|integer|exists:item_codes,id',
                // 'type' => 'required|integer|in:1,2',
            ]);

            if ($validator->fails()) {
                return respondError(
                    $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                    400,
                    $validator->errors()
                );
            }

            $data = $request->all();
            $recipe = $this->recipeService->update($id, $data, $request->file('images'));
            if ($recipe instanceof \Illuminate\Http\JsonResponse) {
                return $recipe;
            }
            return ResponseWithSuccessData($lang, new RecipeResource($recipe), 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'الوصفة غير موجودة' : 'recipe not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching recipe: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $response = $this->recipeService->delete($request, $id);
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                return $response;
            }
            $message = $lang === 'ar' ? 'تم حذف الوصفة بنجاح' : 'Recipe deleted successfully';
            return ResponseWithSuccessData($lang, null, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'الوصفة غير موجودة' : 'recipe not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching recipe: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function listProducts(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $products = $this->recipeService->getAllProducts();
            return ResponseWithSuccessData($lang, $products, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching products: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
