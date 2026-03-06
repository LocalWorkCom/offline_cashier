<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FiledOfStudy;
use App\Services\HR_Services\FiledOfStudyService;
use Illuminate\Http\Request;

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
        $query = $this->FiledOfStudyService->index(false);

        // Dashboard may or may not need pagination
        // $result = paginateOrGetAll($query, $request);

        $FiledOfStudy = $query->get();
        $filedOfStudyCount = FiledOfStudy::count();

        return view('dashboard.filed_of_study.list', compact('FiledOfStudy', 'filedOfStudyCount'));
    }



    public function store(Request $request)
    {
        $response = $this->FiledOfStudyService->store($request, $this->checkToken);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect('dashboard/filed_of_study')->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect('dashboard/filed_of_study')->with('message', $message);
    }

    public function update(Request $request, $id)
    {
        $response = $this->FiledOfStudyService->update($request, $id, $this->checkToken);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect('dashboard/filed_of_study')->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect('dashboard/filed_of_study')->with('message', $message);
    }

    public function delete(Request $request, $id)
    {
        $response = $this->FiledOfStudyService->delete($request, $id, $this->checkToken);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/filed_of_study')->with('message', $message);
    }
}
