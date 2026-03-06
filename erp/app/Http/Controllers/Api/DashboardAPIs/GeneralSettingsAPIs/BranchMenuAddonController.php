<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Models\Branch;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Models\BranchMenuAddon;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Services\KitchenServices\BranchMenuAddonService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BranchMenuAddonController extends Controller
{
    protected $branchMenuAddonService;

    public function __construct(BranchMenuAddonService $branchMenuAddonService)
    {
        $this->branchMenuAddonService = $branchMenuAddonService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $response = $this->branchMenuAddonService->index($request);
        $responseData = $response->original;
        $branch_menu_addons = $responseData['data'];
        $branches = Branch::all();
        return view('dashboard.branch.branch_menu_addon.list', compact('branch_menu_addons', 'branches'));
    }

    public function show(Request $request, $id)
    {
        try {
             $lang = $request->header('lang', 'ar');
            $exists = BranchMenuAddon::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_addon.not_found'), 404);
            }
            $response = $this->branchMenuAddonService->show($id);
            $responseData = $response->original;
            $branchMenuAddonService = $responseData['data'];

            return ResponseWithSuccessData($lang, $branchMenuAddonService, 1);
        }catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = BranchMenuAddon::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_addon.not_found'), 404);
            }
            $response = $this->branchMenuAddonService->update($request, $id);
            $responseData = $response->original;
            if (!$responseData['status'] && (isset($responseData['data']) || isset($responseData['message']))) {
                // Return consistent validation error format
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => isset($responseData['message']) ? $responseData['message'] : 'Validation Error.',
                    'data' => null,
                    'errorData' => $responseData['data'],  // The validation errors
                    'validation_type' => true
                ], 400);
            }

            $message = $responseData['data'];
            return ResponseWithSuccessData($lang, $message, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function change_status(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = BranchMenuAddon::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_addon.not_found'), 404);
            }
            $response = $this->branchMenuAddonService->change_status($id);
            $responseData = $response->original;
            $branch_menu_addon = $responseData['data'];

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function show_branch(Request $request, $branch_id)
    {
        $lang = $request->header('lang', 'ar');
        try {
            $response = $this->branchMenuAddonService->branch($branch_id);
            $query = $response->original['data']; // This is now a query builder

            $branches = Branch::all();

            // Now paginateOrGetAll will work since it receives a query builder
            $paginated = paginateOrGetAll($query, $request, null);
            $paginated['data'] = $paginated['data']->map(function($addon) use($lang) {
                $addon->is_active = $addon->is_active ? ($lang == 'en' ? 'Active' : 'نشط') : ($lang == 'en' ? 'Not Active' : 'غير نشط');
                return $addon;
            });

            return ResponseWithSuccessDataPaginated($lang, $paginated, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
