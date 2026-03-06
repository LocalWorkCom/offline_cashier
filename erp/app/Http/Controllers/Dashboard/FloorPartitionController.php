<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Floor;
use App\Services\SettingsServices\FloorPartitionService;
use Illuminate\Http\Request;

class FloorPartitionController extends Controller
{
    protected $floorPartitionService;

    public function __construct(FloorPartitionService $floorPartitionService)
    {
        $this->floorPartitionService = $floorPartitionService;
    }

    public function fetchFloorsForBranch(Request $request)
    {
        $branchId = $request->branchId;
        $floors = \App\Models\Floor::where('branch_id', $branchId)->get();
        return response()->json($floors);
    }
    public function index(Request $request)
    {
        $response = $this->floorPartitionService->index($request);
        $responseData = $response->original;
        $Partitions = $responseData['data'];
        $floors = Floor::all();
        $branches = Branch::all();
        // If user is Branch Manager, restrict to their branch
        if (auth('admin')->user()->hasRole('Branch Manager')) {
            $branch_id = getBranchManagerID();
            if ($branch_id) {
                $branches = Branch::where('id', $branch_id)->get();
                $floors = Floor::where('branch_id', $branch_id)->get();
            }
        }
        return view('dashboard.floorPartition.list', compact('Partitions', 'floors', 'branches'));
    }

    public function show($id)
    {
        $response = $this->floorPartitionService->show($id);
        $responseData = $response->original;
        return $Partitions = $responseData['data'];
    }

    public function show_data($id)
    {
        $response = $this->floorPartitionService->show_all($id);
        $responseData = $response->original;
        $floorPartitions = $responseData['data'];
        return view('dashboard.floorPartition.show_data', compact('floorPartitions'));
    }

    public function show_all($floor_id)
    {
        $response = $this->floorPartitionService->show_all($floor_id);
        $responseData = $response->original;
        $Partitions = $responseData['data'];
        $floors = Floor::where('id', $floor_id)->get();
        // if (auth('admin')->user()->hasRole('Branch Manager')) {
        //     $branch_id = getBranchManagerID();
        //     if ($branch_id) {
        //         $floors = Floor::where('branch_id', $branch_id)->get();
        //     }
        // }
        return view('dashboard.floorPartition.list', compact('Partitions', 'floors'));
    }

    public function store(Request $request)
    {
        $response = $this->floorPartitionService->add($request);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['message'])) {
            return redirect()->back()->withErrors($responseData['message'])->withInput();
        }
        $message = $responseData['message'];
        return redirect('dashboard/floor-partitions')->with('message', $message);
    }

    public function update(Request $request, $id)
    {
        $response = $this->floorPartitionService->edit($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['message'])) {
            return redirect()->back()->withErrors($responseData['message'])->withInput();
        }
        $message = $responseData['message'];
        return redirect('dashboard/floor-partitions')->with('message', $message);
    }
    public function delete(Request $request, $id)
    {
        $response = $this->floorPartitionService->delete($request, $id);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/floor-partitions')->with('message', $message);
    }
}
