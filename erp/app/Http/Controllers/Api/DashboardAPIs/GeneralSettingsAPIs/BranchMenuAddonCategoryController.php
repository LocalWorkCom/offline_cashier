<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Models\Branch;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Models\BranchMenuAddonCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\KitchenServices\BranchMenuAddonCategoryService;

class BranchMenuAddonCategoryController extends Controller
{
    protected $branchMenuAddonCategoryService;

    public function __construct(BranchMenuAddonCategoryService $branchMenuAddonCategoryService)
    {
        $this->branchMenuAddonCategoryService = $branchMenuAddonCategoryService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $response = $this->branchMenuAddonCategoryService->index($request);
        $responseData = $response->original;
        $branch_menu_categories = $responseData['data'];
        $branches = Branch::all();
        return ResponseWithSuccessDataPaginated($lang, $branch_menu_categories, 1);
    }

    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = BranchMenuAddonCategory::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_addon_category.not_found'), 404);
            }
            $response = $this->branchMenuAddonCategoryService->show($id);
            $responseData = $response->original;
            $branch_menu_addon_category = $responseData['data'];

            return ResponseWithSuccessData($lang, $branch_menu_addon_category, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function change_status(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = BranchMenuAddonCategory::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_addon_category.not_found'), 404);
            }
            $response = $this->branchMenuAddonCategoryService->change_status($id);
            $responseData = $response->original;
            $branch_menu_addon_category = $responseData['data'];

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function show_branch(Request $request, $branch_id)
    {
        $lang = $request->header('lang', 'ar');
        try {

            $query = $this->branchMenuAddonCategoryService->branch($branch_id);
            $response = paginateOrGetAll($query, $request, null);

            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
