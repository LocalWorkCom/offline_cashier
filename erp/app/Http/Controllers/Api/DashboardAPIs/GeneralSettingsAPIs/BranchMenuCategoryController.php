<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Models\Branch;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Models\BranchMenuCategory;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\KitchenServices\BranchMenuCategoryService;

class BranchMenuCategoryController extends Controller
{
    protected $branchMenuCategoryService;

    public function __construct(BranchMenuCategoryService $branchMenuCategoryService)
    {
        $this->branchMenuCategoryService = $branchMenuCategoryService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $response = $this->branchMenuCategoryService->index($request);
        $responseData = $response->original;
        $branch_menu_categories = $responseData['data'];
        $branches = Branch::all();

        $response = paginateOrGetAll($branch_menu_categories, $request);

        return ResponseWithSuccessDataPaginated($lang, $response, 1);
    }

    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = BranchMenuCategory::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            $response = $this->branchMenuCategoryService->show($id);
            $responseData = $response->original;
            $branch_menu_category = $responseData['data'];

            return ResponseWithSuccessData($lang, $branch_menu_category, 1);
        }catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function change_status(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = BranchMenuCategory::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            $response = $this->branchMenuCategoryService->change_status($id);
            $responseData = $response->original;
            $branch_menu_category = $responseData['data'];

            return RespondWithSuccessRequest($lang, 1);
        }catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function show_branch(Request $request, $branch_id)
    {
        try {
            $lang = $request->header('lang', 'ar');

            $query = $this->branchMenuCategoryService->branch($branch_id);
            $response = paginateOrGetAll($query, $request, null);

            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
