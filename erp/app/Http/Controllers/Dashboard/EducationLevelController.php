<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\EducationLevel;
use App\Services\HR_Services\EducationLevelService;
use Illuminate\Http\Request;

class EducationLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

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

        // Pass it to the service
        $response  = $this->EducationLevelService->index($request, $this->checkToken);
        $responseData = json_decode($response->getContent(), true);
        $EducationLevelS = EducationLevel::hydrate($responseData['data']);
        $educationLevelCount = EducationLevel::count();
        return view('dashboard.education_level.list', compact('EducationLevelS','educationLevelCount'));
    }
    

    public function store(Request $request)
    {
        $response = $this->EducationLevelService->store($request, $this->checkToken);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect('dashboard/education_level')->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect('dashboard/education_level')->with('message',$message);
    }

    public function update(Request $request, $id)
    {
        $response = $this->EducationLevelService->update($request, $id, $this->checkToken);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect('dashboard/education_level')->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect('dashboard/education_level')->with('message',$message);
    }

    public function delete(Request $request, $id)
    {
        $response = $this->EducationLevelService->delete($request, $id, $this->checkToken);
        $responseData = $response->original;
        $message= $responseData['message'];
        return redirect('dashboard/education_level')->with('message',$message);
    }
}
