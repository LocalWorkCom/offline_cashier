<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\HR_Services\MaritalStatusService;
use Illuminate\Http\Request;

class MaritalStatusController extends Controller
{
    protected $maritalStatusService;
    protected $checkToken;


    public function __construct(MaritalStatusService $maritalStatusService)
    {
        $this->maritalStatusService = $maritalStatusService;
        $this->checkToken = false;
    }

    public function index()
    {
        $maritalStatuses = $this->maritalStatusService->index()->get();
        return view('dashboard.maritalStatus.index', compact('maritalStatuses'));
    }

    public function store(Request $request)
    {
        $this->maritalStatusService->store($request, $this->checkToken);
        return redirect()->route('marital_statuses.list')->with('success', 'Marital Status created successfully!');
    }

    public function update(Request $request, $id)
    {

        $this->maritalStatusService->update($request, $id, $this->checkToken);
        return redirect()->route('marital_statuses.list')->with('success', 'Marital Status updated successfully!');
    }
    public function delete(Request $request, $id)
    {
        $this->maritalStatusService->delete( $id);
        return redirect()->route('marital_statuses.list')->with('success', 'Marital Status deleted successfully!');
    }
}
