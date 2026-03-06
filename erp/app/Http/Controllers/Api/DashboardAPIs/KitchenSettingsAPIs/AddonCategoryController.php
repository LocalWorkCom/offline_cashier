<?php

namespace App\Http\Controllers\Api\DashboardAPIs\KitchenSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Services\KitchenServices\AddonCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AddonCategoryController extends Controller
{
    protected $addonCategoryService;
    protected $lang;

    protected             $fields = ['description', 'name_site', 'description_site', 'created_at', 'updated_at', 'deleted_at'];

    public function __construct(AddonCategoryService $addonCategoryService, Request $request)
    {
        $this->addonCategoryService = $addonCategoryService;
        $this->lang = $request->header('lang', 'ar');
    }
    public function index(Request $request)
    {
        try {
           $withTrashed = $request->query('withTrashed', false);
            $query = $this->addonCategoryService->index($withTrashed);
            $response = paginateOrGetAll($query, $request, $this->fields);

            return ResponseWithSuccessDataPaginated($this->lang, $response, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching addon categories: ' . $e->getMessage());
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    private function makeHiddenFields($model)
    {
        $fields = ['name', 'description', 'name_site', 'description_site', 'created_at', 'updated_at', 'deleted_at'];
        if ($model instanceof \Illuminate\Support\Collection) {
            $model->each->makeHidden($fields);
        } elseif ($model instanceof \Illuminate\Database\Eloquent\Model) {
            $model->makeHidden($fields);
        }
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $addonCategory = $this->addonCategoryService->show($id);
            $this->makeHiddenFields($addonCategory);

            return ResponseWithSuccessData($this->lang, $addonCategory, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'فئات لأضافات غير موجودة' : 'addon caegory not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching recipe: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $addonCategory = $this->addonCategoryService->store($request->all());

        // If the service returned a JsonResponse (error), return it directly
        if ($addonCategory instanceof \Illuminate\Http\JsonResponse) {
            return $addonCategory;
        }

        return ResponseWithSuccessData($this->lang, $addonCategory->makeHidden($this->fields), 1);
    }


    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $addonCategory = $this->addonCategoryService->update($id, $request->all());

            if ($addonCategory instanceof \Illuminate\Http\JsonResponse) {
                return $addonCategory;
            }

            return ResponseWithSuccessData($this->lang, $addonCategory->makeHidden($this->fields), 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'فئات لأضافات غير موجودة' : 'addon caegory not found';
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
            $response = $this->addonCategoryService->delete($id, $this->lang);

            // If response is a JSON error (bad request, etc.), return it directly
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                return $response;
            }

            // Return success response
            $message = $this->lang === 'ar' ? 'تم حذف الفئة بنجاح' : 'Category deleted successfully';
            return ResponseWithSuccessData($this->lang, null, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'فئات لأضافات غير موجودة' : 'addon caegory not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching recipe: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function restore($id)
    {
        $addonCategory = $this->addonCategoryService->restore($id);
        return response()->json($addonCategory);
    }
}
