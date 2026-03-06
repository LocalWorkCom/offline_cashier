<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Complaint;
use App\Models\Floor;
use App\Models\FloorPartition;
use App\Services\ComplaintsService;
use App\Services\SettingsServices\TableService;
use Illuminate\Http\Request;

class ComplaintsController extends Controller
{
    protected $complaintsService;

    public function __construct(ComplaintsService $complaintsService)
    {
        $this->complaintsService = $complaintsService;
    }

    public function index(Request $request)
    {
        // Get complaints with all needed relationships
        $complaints = Complaint::with([
            'client',
            'order.branch'
        ])
            ->where('manage', 'admin')
            ->get();

        return view('dashboard.complaints.list', compact('complaints'));
    }

    public function show(Request $request, $id)
    {
        $response = $this->complaintsService->show($request, $id);
        $responseData = $response->original;
        $complaint = $responseData['data'];
        return view('dashboard.complaints.show', compact('complaint'));
    }

    public function edit($id)
    {
        $complaint = Complaint::findOrFail($id);

        return view('dashboard.complaints.edit', compact('complaint'));
    }

    public function changeStatus(Request $request, $id)
    {
        $response = $this->complaintsService->changeStatus($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['message'])) {
            return redirect()->back()->withErrors($responseData['message'])->withInput();
        }
        $message = $responseData['message'];
        return redirect('dashboard/complaints')->with('message', $message);
    }

    public function delete(Request $request, $id)
    {
        $response = $this->complaintsService->destroy($request, $id);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/complaints')->with('message', $message);
    }
}
