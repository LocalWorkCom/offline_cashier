<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Models\Branch;
use App\Models\Country;
use App\Models\BranchMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Services\KitchenServices\BranchMenuService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BranchMenuController extends Controller
{
    protected $branchMenuService;

    public function __construct(BranchMenuService $branchMenuService)
    {
        $this->branchMenuService = $branchMenuService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $response = $this->branchMenuService->index($request);
        $responseData = $response->original;
        $branch_menu_categories = $responseData['data'];
        $branches = Branch::all();
        return ResponseWithSuccessDataPaginated($lang, $branch_menu_categories, 1);
    }

    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = BranchMenu::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu.not_found'), 404);
            }
            $response = $this->branchMenuService->show($id);
            $responseData = $response->original;
            $branch_menu = $responseData['data'];

            return ResponseWithSuccessData($lang, $branch_menu, 1);
        }catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = BranchMenu::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu.not_found'), 404);
            }
            $response = $this->branchMenuService->update($request, $id);
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
            $exists = BranchMenu::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu.not_found'), 404);
            }
            $response = $this->branchMenuService->change_status($id);
            $responseData = $response->original;
            $branch_menu = $responseData['data'];

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function show_branch(Request $request, $branch_id)
    {
        $lang = $request->header('lang', 'ar');
        try {

            $branch_menu_categories = $this->branchMenuService->branch($branch_id);
            $branches = Branch::all();

            $response = paginateOrGetAll($branch_menu_categories, $request, null);

            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
