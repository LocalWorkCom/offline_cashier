<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Models\Floor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\SettingsServices\MenusIntegrationService;

class MenusIntegrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected $menusIntegrationService;

    public function __construct(MenusIntegrationService $menusIntegrationService)
    {
        $this->menusIntegrationService = $menusIntegrationService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        // try {
            $employee = auth()->user();
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 4);
            }
            $data = $this->menusIntegrationService->index($request);
            $fields = ['name_site'];
            $visible = ['name_ar', 'name_en'];
            $response = paginateOrGetAll($data, $request, $fields, $visible);
            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        // } catch (\Exception $e) {
        //     return RespondWithBadRequestData($lang, 2);
        // }
    }

    public function add(Request $request)
    {
        
    }

    public function edit(Request $request)
    {
        
    }

    public function delete(Request $request, $id)
    {
        
    }

    public function show(Request $request, $id)
    {
        
    }
}
