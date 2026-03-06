<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\violation_type;
use App\Models\ViolationType;
use App\Services\HR_Services\ViolationTypeService;
use Illuminate\Http\Request;

class ViolationTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    protected $ViolationType;
    protected $checkToken;
    protected $lang;

    public function __construct(ViolationTypeService $ViolationType)
    {
        $this->ViolationType = $ViolationType;
        $this->checkToken = false;
        $this->lang =  app()->getLocale();
    }

    public function index(Request $request)
    {

        // Pass it to the service
        $response  = $this->ViolationType->index($request, $this->checkToken);
        $responseData = json_decode($response->getContent(), true);
        $violationTypes = ViolationType::hydrate($responseData['data']);

        return view('dashboard.violation_type.list', compact('violationTypes'));
    }

    public function store(Request $request)
    {
        $response = $this->ViolationType->store($request, $this->checkToken);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect('dashboard/violation_types')->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect('dashboard/violation_types')->with('message',$message);
    }

    public function update(Request $request, $id)
    {
        $response = $this->ViolationType->update($request, $id, $this->checkToken);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect('dashboard/violation_types')->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect('dashboard/violation_types')->with('message',$message);
    }

    public function delete(Request $request, $id)
    {
        $response = $this->ViolationType->delete($request, $id, $this->checkToken);
        $responseData = $response->original;
        $message= $responseData['message'];
        return redirect('dashboard/violation_types')->with('message',$message);
    }
}
