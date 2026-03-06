<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Models\EducationLevel;
use App\Services\SettingsServices\EducationLevelService;
use Illuminate\Http\Request;

class EducationLevelController extends Controller
{
    protected $EducationLevelService;
    protected $checkToken;
    protected $lang;

    public function __construct(EducationLevelService $EducationLevelService)
    {
        $this->EducationLevelService = $EducationLevelService;
        $this->checkToken = false;
        $this->lang =  app()->getLocale();
    }
    public function index(Request $request)
    {
        $response  = $this->EducationLevelService->index($request, $this->checkToken);
        $responseData = json_decode($response->getContent(), true);
        // $EducationLevelS = EducationLevel::hydrate($responseData['data']);
        $EducationLevelS = $responseData['data'];
        $educationLevelCount = EducationLevel::count();
        $responseData = [
            'EducationLevels' => $EducationLevelS,
            'educationLevelCount' => $educationLevelCount
        ];
        // $data = ['data' => $responseData];
        return ResponseWithSuccessDataPaginated($this->lang, $EducationLevelS, 1);
    }
    public function show(Request $request, $id)
    {
        $response  = $this->EducationLevelService->show($request, $this->checkToken, $id);
        if (!$response) {
            return respondError($this->lang == 'en' ? 'education level not found.' : 'المستوي التعليمي غير موجود', 404);
        }
        // dd($response);
        return ResponseWithSuccessData($this->lang, $response, 1);
    }

    public function store(Request $request)
    {
        $response = $this->EducationLevelService->store($request, $this->checkToken);
        $responseData = $response->original;

        // Check if the response indicates success
        if ($responseData['status']) {
            // Return the success response with the created data
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => $responseData['message'],
                'data' => $responseData['data']
            ], status: 200);
        } else {
            // Handle error case
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $this->lang == 'en' ? 'name is already exist' : 'الاسم مأخوذ مسبقا.',
                'errorData' => ['error' => $responseData['data']],
                'data' => null
            ], status: 400);
        }
    }

    public function update(Request $request, $id)
    {
        $response = $this->EducationLevelService->update($request, $id, $this->checkToken);
        $responseData = $response->original;

        $educationLevel = EducationLevel::find($id);
        if (!$educationLevel) {
            return respondError($this->lang == 'en' ? 'education level not found.' : 'المستوي التعليمي غير موجود', 404);
        }

        if (!$responseData['status']) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $this->lang == 'en' ? 'name is already exist' : 'الاسم مأخوذ مسبقا.',
                'errorData' => ['error' => $responseData['data']],
                'data' => null
            ], 400);
        }

        if ($responseData['code'] === 400) {
            return respondError(
                $this->lang == 'en' ? 'There are no name changes already in place.' : 'لا يوجد اي تغيرات الاسماء موجوده بالفعل',
                404
            );
        }

        // ✅ Return updated record in response
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => $responseData['message'],
            'data' => $responseData['data'], // <-- updated EducationLevel
        ], 200);
    }

    public function delete(Request $request, $id)
    {
        $response = $this->EducationLevelService->delete($request, $id, $this->checkToken);
        $responseData = $response->original;
        if ($responseData['code'] === 400) {
            return respondError($this->lang == 'en' ? 'education level not found.' : 'المستوي التعليمي غير موجود', 404);
        }
        return RespondWithSuccessRequest($this->lang,  1);
    }
}
