<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Models\FiledOfStudy;
use App\Services\HR_Services\FiledOfStudyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FiledOfStudyController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    protected $FiledOfStudyService;
    protected $checkToken;
    protected $lang;

    public function __construct(FiledOfStudyService $FiledOfStudyService)
    {
        $this->FiledOfStudyService = $FiledOfStudyService;
        $this->checkToken = false;
        $this->lang =  app()->getLocale();
    }

    public function index(Request $request)
    {
        $lang = app()->getLocale();

        // ✅ Validation only for API
        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }

        $query = $this->FiledOfStudyService->index(true);
        $visibleColumns = ['id', 'name_en', 'name_ar', 'employees_count'];
        $hiddenColumns = ['name'];
        $result = paginateOrGetAll($query, $request, $hiddenColumns, $visibleColumns);
        return ResponseWithSuccessDataPaginated($lang, $result, 1);
    }


    public function store(Request $request)
    {
        $lang = app()->getLocale();


        $response = $this->FiledOfStudyService->store($request, $this->checkToken);
        $responseData = $response->original;

        if (!$responseData['status']) {
            return $response;
        }
        return ResponseWithSuccessData($lang, $responseData['data'], 1);
    }

    public function update(Request $request, $id)
    {
        $lang = app()->getLocale();
      
        $response = $this->FiledOfStudyService->update($request, $id, $this->checkToken);
        $responseData = $response->original;

        if (!$responseData['status']) {
            return $response;
        }
        return ResponseWithSuccessData($lang, $responseData['data'], 1);
    }

    public function delete(Request $request, $id)
    {
     
        $response = $this->FiledOfStudyService->delete($request, $id, $this->checkToken);
        $responseData = $response->original;
        if (!$responseData['status']) {
            return $response;
        } else {
            $data = $responseData['message'];
        }
        return RespondWithSuccessMsg($data);
    }
}
