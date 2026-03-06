<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Models\Branch;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Models\BranchMenuSize;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Services\KitchenServices\BranchMenuSizeService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BranchMenuSizeController extends Controller
{
    protected $branchMenuSizeService;

    public function __construct(BranchMenuSizeService $branchMenuSizeService)
    {
        $this->branchMenuSizeService = $branchMenuSizeService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $response = $this->branchMenuSizeService->index($request);
        $responseData = $response->original;
        $branch_menu_sizes = $responseData['data'];
        $branches = Branch::all();
        return ResponseWithSuccessDataPaginated($lang, $branch_menu_sizes, 1);
    }

    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = BranchMenuSize::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_size.not_found'), 404);
            }
            $response = $this->branchMenuSizeService->show($id);
            $responseData = $response->original;
            $branchMenuSizeService = $responseData['data'];

            return ResponseWithSuccessData($lang, $branchMenuSizeService, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = BranchMenuSize::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_size.not_found'), 404);
            }
            $response = $this->branchMenuSizeService->update($request, $id);
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
            $exists = BranchMenuSize::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_size.not_found'), 404);
            }
            $response = $this->branchMenuSizeService->change_status($id);
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
            $response = $this->branchMenuSizeService->branch($branch_id);
            $query = $response->original['data']; // This is now a query builder

            $branches = Branch::all();

            // Now paginateOrGetAll will work since it receives a query builder
            $paginated = paginateOrGetAll($query, $request, null);
            $paginated['data'] = $paginated['data']->map(function($size) use($lang) {
                $size->is_active = $size->is_active ? ($lang == 'en' ? 'Active' : 'نشط') : ($lang == 'en' ? 'Not Active' : 'غير نشط');
                return $size;
            });

            return ResponseWithSuccessDataPaginated($lang, $paginated, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
