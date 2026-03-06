<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Services\ReportServices\FeedbackReportService;
use App\Services\FeedbackService;
use Illuminate\Http\Request;

class FeedbackReportController extends Controller
{
    protected $complaintsService;

    public function __construct(FeedbackReportService $complaintsService)
    {
        $this->complaintsService = $complaintsService;
    }

    public function index(Request $request)
    {
//        dd(app()->getLocale());
        $response = $this->complaintsService->index($request);

        $responseData = $response->original;

        $complaints = $responseData['data'];
        $pendingCount = Complaint::where('status', 'pending')->count();
        $inprogressCount = Complaint::where('status', 'inprogress')->count();
        $solvedCount = Complaint::where('status', 'solved')->count();

        return view('dashboard.reports.feedbacks.list', compact('complaints', 'pendingCount', 'inprogressCount', 'solvedCount'));
    }

    public function show(Request $request, $id)
    {
        $response = $this->complaintsService->show($request, $id);
        $responseData = $response->original;
        $complaint = $responseData['data'];
        return view('dashboard.reports.feedbacks.show', compact('complaint'));
    }

//     public function show(Request $request, $id)
// {
//     $response = $this->complaintsService->show($request, $id);
    
//     if ($response instanceof \Illuminate\Http\JsonResponse) {
//         $responseData = $response->getData(true);
//         $complaint = $responseData['data'] ?? $responseData;
//     } else {
//         $complaint = $response;
//     }
    
//     return view('your.view.name', compact('complaint'));
// }

    public function edit($id)
    {
        $complaint = Complaint::findOrFail($id);

        return view('dashboard.reports.feedbacks.edit', compact('complaint'));
    }

    public function changeStatus(Request $request, $id)
    {
        $response = $this->complaintsService->changeStatus($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['message'])) {
            return redirect()->back()->withErrors($responseData['message'])->withInput();
        }
        $message= $responseData['message'];
        return redirect('dashboard/reports/feedbacks')->with('message',$message);
    }

    public function delete(Request $request, $id)
    {
        $response = $this->complaintsService->destroy($request, $id);
        $responseData = $response->original;
        $message= $responseData['message'];
        return redirect('dashboard/reports/feedbacks')->with('message',$message);
    }
}
