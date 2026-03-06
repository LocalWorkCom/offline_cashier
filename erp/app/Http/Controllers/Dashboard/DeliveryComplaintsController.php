<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Complaint;
use App\Models\DeliveryComplaints;
use App\Models\Floor;
use App\Models\FloorPartition;
use App\Services\ComplaintsService;
use App\Services\DeliveryComplaintsService;
use App\Services\SettingsServices\TableService;
use Illuminate\Http\Request;

class DeliveryComplaintsController extends Controller
{
    protected $complaintsService;

    public function __construct(DeliveryComplaintsService $complaintsService)
    {
        $this->complaintsService = $complaintsService;
    }

    public function index(Request $request)
    {
//        dd(app()->getLocale());
        $response = $this->complaintsService->index($request);

        $responseData = $response->original;

        $complaints = $responseData['data'];

        return view('dashboard.hanging_orders.list', compact('complaints'));
    }

    public function show(Request $request, $id)
    {
        $response = $this->complaintsService->show($request, $id);
        $responseData = $response->original;
        $complaint = $responseData['data'];
        return view('dashboard.hanging_orders.show', compact('complaint'));
    }

    public function edit($id)
    {
        $complaint = DeliveryComplaints::findOrFail($id);

        return view('dashboard.hanging_orders.edit', compact('complaint'));
    }

    public function changeStatus(Request $request, $id)
    {
        $response = $this->complaintsService->changeStatus($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['message'])) {
            return redirect()->back()->withErrors($responseData['message'])->withInput();
        }
        $message = $responseData['message'];
        return redirect('dashboard/hanging-orders')->with('message', $message);
    }

    public function delete(Request $request, $id)
    {
        $response = $this->complaintsService->destroy($request, $id);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/hanging-orders')->with('message', $message);
    }
}
