<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Country;
use App\Models\University;
use Illuminate\Http\Request;
use App\Services\HR_Services\UniversityService;
use App\Http\Controllers\Controller;

class UniversityController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    protected $UniversityService;
    protected $checkToken;
    protected $lang;
    protected $visibleFields = ['name_en', 'name_ar', 'logo'];

    public function __construct(UniversityService $UniversityService)
    {
        $this->UniversityService = $UniversityService;
        $this->checkToken = false;
        $this->lang =  app()->getLocale();
    }

    public function index(Request $request)
    {
        $isManager = auth('admin')->user()->hasRole('branch Manager');
        $branchId = $isManager ? auth('admin')->user()->branch_id : null;

        $universities = $this->UniversityService
            ->indexQuery($isManager, $branchId)
            ->get()->makeVisible($this->visibleFields);

        $TotalUniversity = University::count();
        $countries = Country::all(); 

        return view('dashboard.university.list', [
            'university' => $universities,
            'TotalUniversity' => $TotalUniversity,
            'countries' => $countries,
        ]);
    }


    public function store(Request $request)
    {
        $response = $this->UniversityService->store($request);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect('dashboard/university')->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect('dashboard/university')->with('message', $message);
    }

    public function update(Request $request, $id)
    {

        $response = $this->UniversityService->update($request, $id);
        $responseData = $response->original;

        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect('dashboard/university')->withErrors($validationErrors)->withInput();
        }

        $message = $responseData['message'];
        return redirect('dashboard/university')->with('message', $message);
    }

    public function delete(Request $request, $id)
    {
        $response = $this->UniversityService->delete( $id);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/university')->with('message', $message);
    }
}
