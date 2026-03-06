<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Services\HR_Services\MilitaryServiceStatusService;
use Illuminate\Http\Request;

class MilitaryServiceStatusController extends Controller
{
    protected $militaryServiceStatusService;
    protected $checkToken;


    public function __construct(MilitaryServiceStatusService $militaryServiceStatusService)
    {
        $this->militaryServiceStatusService = $militaryServiceStatusService;
        $this->checkToken = false;
    }

    public function index()
    {
        $militaryStatuses = $this->militaryServiceStatusService->index()->get();
        $countries = Country::all();
        return view('dashboard.militaryServiceStatus.index', compact('militaryStatuses', 'countries'));	
    }

    public function store(Request $request)
    {
        $this->militaryServiceStatusService->store($request);
        return redirect()->route('military_service_statuses.list')->with('success', 'Military Service Status created successfully!');
    }

    public function update(Request $request, $id)
    {

        $this->militaryServiceStatusService->update($request, $id);
        return redirect()->route('military_service_statuses.list')->with('success', 'Military Service Status updated successfully!');
    }
    public function delete($id)
    {
        $this->militaryServiceStatusService->delete($id);
        return redirect()->route('military_service_statuses.list')->with('success', 'Military Service Status deleted successfully!');
    }
}
