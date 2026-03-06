<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\DeliveryComplaints;
use App\Services\ReportServices\DeliveryComplaintsReportService;
use Illuminate\Http\Request;

class DeliveryComplaintsReportsController extends Controller
{
    protected $complaintsService;

    public function __construct(DeliveryComplaintsReportService $complaintsService)
    {
        $this->complaintsService = $complaintsService;
    }

    public function index(Request $request)
    {
//        dd(app()->getLocale());
        $complaints = $this->complaintsService->index($request,app()->getLocale());


        return view('dashboard.reports.hanging_orders.list', compact('complaints'));
    }

    public function show(Request $request, $id)
    {
        $complaint = $this->complaintsService->show($request, $id,app()->getLocale());
        
        return view('dashboard.reports.hanging_orders.show', compact('complaint'));
    }

    public function edit($id)
    {
        $complaint = DeliveryComplaints::findOrFail($id);

        return view('dashboard.reports.hanging_orders.edit', compact('complaint'));
    }

    public function changeStatus(Request $request, $id)
    {
        $response = $this->complaintsService->changeStatus($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['message'])) {
            return redirect()->back()->withErrors($responseData['message'])->withInput();
        }
        $message= $responseData['message'];
        return redirect('dashboard/reports/hanging-orders')->with('message',$message);
    }

    public function delete(Request $request, $id)
    {
        $response = $this->complaintsService->destroy($request, $id);
        $responseData = $response->original;
        $message= $responseData['message'];
        return redirect('dashboard/reports/hanging-orders')->with('message',$message);
    }
}
