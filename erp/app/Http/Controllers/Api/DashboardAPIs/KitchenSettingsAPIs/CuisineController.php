<?php

namespace App\Http\Controllers\Api\DashboardAPIs\KitchenSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Models\Cuisine;
use App\Services\KitchenServices\CuisineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CuisineController extends Controller
{
    protected $cuisineService;
    protected $hiddenFildes=['name_site','description','description_site', 'image_path'];
    protected $visibleFields=['image','name_ar','name_en','description_ar','description_en'];

    public function __construct(CuisineService $cuisineService)
    {
        $this->cuisineService = $cuisineService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $withTrashed = $request->query('withTrashed', false);

        $query = $this->cuisineService->index($withTrashed);
        $response = paginateOrGetAll($query, $request, $this->hiddenFildes, $this->visibleFields);
        $response['data'] = $response['data']->map(function($cuisine) use($lang) {
            $cuisine->is_active = $cuisine->is_active == 1 ? ($lang == 'en' ? 'Active' : 'نشط') : ($lang == 'en' ? 'Not Active' : 'غير نشط');
            return $cuisine;
        });

        return ResponseWithSuccessDataPaginated($lang, $response, 1);
    }


    public function show($id)
    {
        try {
            $lang = request()->header('lang', 'ar');
            $exists = Cuisine::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('cuisines.not_found'), 404);
            }
            $cuisine = $this->cuisineService->show($id)->makeHidden($this->hiddenFildes)->makeVisible($this->visibleFields);
            $cuisine->is_active = $cuisine->is_active == 1 ? ($lang == 'en' ? 'Active' : 'نشط') : ($lang == 'en' ? 'Not Active' : 'غير نشط');
            return ResponseWithSuccessData($lang, $cuisine, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching cuisine: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function store(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $messages = [];

            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'description_ar' => 'required|string',
                'description_en' => 'required|string',
                'is_active' => 'required|boolean',
                'image' => 'nullable|image|mimes:jpg,png,jpeg',
            ], $messages);
            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }
            $cuisine =  $this->cuisineService->store($validator->validated(), $request->file('image'));
            return ResponseWithSuccessData($lang, $cuisine->makeHidden($this->hiddenFildes)->makeVisible($this->visibleFields), 1);
        } catch (\Exception $e) {
            Log::error('Error creating cuisine: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $messages = [];
            $exists = Cuisine::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('cuisines.not_found'), 404);
            }

            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string|max:255',
                'name_en' => 'nullable|string|max:255',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'is_active' => 'required|boolean',
                'image' => 'nullable|image|mimes:jpg,png,jpeg|max:5000',
            ], $messages);

            if ($validator->fails()) {
                return respondError(
                    $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                    400,
                    $validator->errors()
                );
            }
            $cuisine = $this->cuisineService->update($id, $validator->validated(), $request->file('image'));

            return ResponseWithSuccessData($lang, $cuisine->makeHidden($this->hiddenFildes)->makeVisible($this->visibleFields), 1);
        } catch (\Exception $e) {
            Log::error('Error updating cuisine: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function destroy($id)
    {
        $lang = request()->header('lang', 'ar');
        try {
            $exists = Cuisine::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('cuisines.not_found'), 404);
            }
            $this->cuisineService->delete($id);

            return ResponseWithSuccessData($lang, null, 1);
        } catch (\Exception $e) {
            Log::error('Error deleting cuisine: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function restore($id)
    {
        try {
            $lang = request()->header('lang', 'ar');
            $exists = Cuisine::where('id', $id)->withTrashed()->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('cuisines.not_found'), 404);
            }
            $cuisine = $this->cuisineService->restore($id)->makeHidden($this->hiddenFildes)->makeVisible($this->visibleFields);

            return ResponseWithSuccessData($lang, $cuisine, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring cuisine: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function assignDishCategories(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $exists = Cuisine::where('id', $id)->exists();
        App::setLocale($lang);
        if (!$exists) {
            return respondError(__('cuisines.not_found'), 404);
        }
        // Validate cuisine ID and dish_categories array
        $validator = Validator::make(
            $request->all(),
            [
                'dish_categories' => 'required|array|min:1',
                'dish_categories.*' => 'integer|exists:dish_categories,id',
            ]
        );

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        try {
            $cuisine = $this->cuisineService->assignCuisineCategory($request, $id);

            return ResponseWithSuccessData($lang, $cuisine->makeHidden($this->hiddenFildes)->makeVisible($this->visibleFields), 1);
        } catch (\Exception $e) {
            Log::error('Error assigning dish categories: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
