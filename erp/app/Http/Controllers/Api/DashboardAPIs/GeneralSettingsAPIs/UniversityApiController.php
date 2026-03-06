<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Resources\UniversityResource;
use App\Services\HR_Services\UniversityService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\University;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UniversityApiController extends Controller
{
    protected $UniversityService;
    protected $visibleFields = ['name_en', 'name_ar', 'logo'];
    protected $hiddenFields = ['created_at', 'updated_at', 'deleted_at', 'name'];


    public function __construct(UniversityService $UniversityService)
    {
        $this->UniversityService = $UniversityService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $isManager = auth('employee')->user()->hasRole('branch_Manager');
            $branchId = $isManager ? auth('employee')->user()->branch_id : null;

            $universities = $this->UniversityService
                ->indexQuery($isManager, $branchId);


            $response = paginateOrGetAll($universities, $request, null);
            $resourceData = UniversityResource::collection($response['data']);

            return ResponseWithSuccessDataPaginated(
                $lang,
                [
                    'data' => $resourceData,
                    'meta' => $response['meta']
                ],
                1
            );
        } catch (\Exception $e) {
            Log::error('Error fetching university: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function store(Request $request)
    {
        try {

            $lang = $request->header('lang', 'ar');
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }

            $result =  $this->UniversityService->store($request);
            if ($result instanceof \Illuminate\Http\JsonResponse) {
                return $result;
            }
            return  RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            $lang = $request->header('lang', 'ar');
            Log::error('Error creating dish category: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            $university = $this->UniversityService->show($id)->makeVisible($this->visibleFields);

            return ResponseWithSuccessData($lang, new UniversityResource($university), 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'الجامعة غير موجودة' : 'University not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching university: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function update(Request $request, $id)
{
    $lang = $request->header('lang', 'ar');

    try {
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }

        // Check if university exists before going to service
        $university = University::find($id);
        if (!$university) {
            $message = $lang === 'ar' ? 'الجامعة غير موجودة' : 'University not found';
            return respondError($message, 404);
        }

        // Merge id into request
        $request->merge(['id' => $id]);

        $data = $this->UniversityService->update($request, $id);

        if ($data instanceof \Illuminate\Http\JsonResponse) {
            return $data; // validation errors
        }

        return RespondWithSuccessRequest($lang, 1);

    } catch (\Exception $e) {
        Log::error('Error updating university: ' . $e->getMessage());
        return RespondWithBadRequestData($lang, 2);
    }
}



       public function destroy(Request $request, $id)
    {
         try {
            $lang = $request->header('lang', 'ar');

            $result = $this->UniversityService->delete($id);

            if ($result instanceof \Illuminate\Http\JsonResponse) {
                return $result;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'الجامعة غير موجودة' : 'University not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching university: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
