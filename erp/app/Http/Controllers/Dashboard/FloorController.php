<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Floor;
use App\Models\Branch;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\SettingsServices\FloorService;

class FloorController extends Controller
{
    protected $floorService;

    public function __construct(FloorService $floorService)
    {
        $this->floorService = $floorService;
    }

    public function index(Request $request)
    {
        $response = $this->floorService->index($request);
        $responseData = $response->original;
        $Floors = $responseData['data']['floors'];
        $branches = $responseData['data']['branches'];

        return view('dashboard.floor.list', compact('Floors', 'branches'));
    }
    public function show($id)
    {
        $response = $this->floorService->show($id);
        $responseData = $response->original;
        return $Floors = $responseData['data'];
    }

    public function store(Request $request)
    {
        //        dd($request->all());
        $response = $this->floorService->add($request);
        $responseData = $response->original;
        //        dd($responseData);
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->route('floors.list')->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect()->route('floors.list')->with('message', $message);
    }

    public function update(Request $request, $id)
    {
        $response = $this->floorService->edit($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->route('floors.list')->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect()->route('floors.list')->with('message', $message);
    }
    public function delete(Request $request, $id)
    {
        $response = $this->floorService->delete($request, $id);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect()->route('floors.list')->with('message', $message);
    }

    public function branch($branch_id)
    {
        // Get branches based on user role
        if (auth('admin')->user()->hasRole('Branch Manager')) {
            $manager_branch_id = getBranchManagerID();
            if ($manager_branch_id) {
                // Force the branch_id to be the manager's branch
                $branch_id = $manager_branch_id;
                // Only get the manager's branch
                $branches = Branch::where('id', $branch_id)->get();
            } else {
                // If no branch ID found for manager, return empty
                $branches = collect();
            }
        } else {
            // For non-manager users, get all branches
            $branches = Branch::all();
        }

        $response = $this->floorService->branch($branch_id);
        $responseData = $response->original;
        $Floors = $responseData['data'];

        return view('dashboard.floor.list', compact('Floors', 'branches'));
    }
}
