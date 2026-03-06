<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Floor;
use App\Models\Table;
use App\Models\Branch;
use Illuminate\Http\Request;
use App\Models\FloorPartition;
use App\Http\Controllers\Controller;
use App\Services\SettingsServices\TableService;

class TableController extends Controller
{
    protected $tableService;

    public function __construct(TableService $tableService)
    {
        $this->tableService = $tableService;
    }

    public function index(Request $request)
    {
        // Get the base response from the service
        $response = $this->tableService->index($request);
        $responseData = $response->original;

        // Start with all tables
        $query = Table::with(['floors', 'floorPartitions', 'floors.branches']);

        // Apply branch filter if requested
        if ($request->has('branch_id') && $request->branch_id) {
            $query->whereHas('floors.branches', function ($q) use ($request) {
                $q->where('id', $request->branch_id);
            });
        }
        $floors = Floor::all();
        $floorPartitions = FloorPartition::all();
        $branches = Branch::all();
        // Restrict to Branch Manager's branch if applicable
        if (auth('admin')->user()->hasRole('Branch Manager')) {
            $branch_id = getBranchManagerID();
            if ($branch_id) {
                $query->whereHas('floors', function ($q) use ($branch_id) {
                    $q->where('branch_id', $branch_id);
                });
                $floors = Floor::where('branch_id', $branch_id)->get();
                // $floorPartitions = FloorPartition::where('branch_id',$branch_id)->get();

                $branches = Branch::where('id', $branch_id)->get();
            }
        }

        // Get the filtered results
        $Tables = $query->get();



        return view('dashboard.table.list', compact('Tables', 'floors', 'floorPartitions', 'branches'));
    }
    public function show($id)
    {
        $response = $this->tableService->show($id);
        $responseData = $response->original;
        return $Tables = $responseData['data'];
    }

    public function show_all($floor_id, $type)
    {
        $response = $this->tableService->show_all($floor_id, $type);
        $responseData = $response->original;
        $Tables = $responseData['data'];
        $floors = Floor::all();
        $floorPartitions = FloorPartition::all();
        if (auth('admin')->user()->hasRole('Branch Manager')) {
            $branch_id = getBranchManagerID();
            if ($branch_id) {
                $floors = Floor::where('branch_id', $branch_id)->get();
                $branches = Branch::where('id', $branch_id)->get();
            }
        }

        return view('dashboard.table.list', compact('Tables', 'floors', 'floorPartitions'));
    }

    public function store(Request $request)
    {
        $response = $this->tableService->add($request);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['message'])) {
            return redirect()->back()->withErrors($responseData['message'])->withInput();
        }
        $message = $responseData['message'];
        return redirect('dashboard/tables')->with('message', $message);
    }

    public function update(Request $request, $id)
    {
        $response = $this->tableService->edit($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['message'])) {
            return redirect()->back()->withErrors($responseData['message'])->withInput();
        }
        $message = $responseData['message'];
        return redirect('dashboard/tables')->with('message', $message);
    }
    public function delete(Request $request, $id)
    {
        $response = $this->tableService->delete($request, $id);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/tables')->with('message', $message);
    }
}
