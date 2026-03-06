<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\HR_Services\LeaveTypeService;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    protected $leaveTypeService;

    public function __construct(LeaveTypeService $leaveTypeService)
    {
        $this->leaveTypeService = $leaveTypeService;
    }

    public function index(Request $request)
    {
        $response = $this->leaveTypeService->index($request);
        $LeaveType = $response->get();
        return view('dashboard.leave_type.list', compact('LeaveType'));
    }

    public function show($id)
    {
        $response = $this->leaveTypeService->show($id);
        $responseData = $response->original;
        return $LeaveType = $responseData['data'];
    }

    public function store(Request $request)
    {
        $response = $this->leaveTypeService->add($request);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->route('leave-types.list')->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect()->route('leave-types.list')->with('message',$message);
    }

    public function update(Request $request, $id)
    {
        $response = $this->leaveTypeService->edit($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->route('leave-types.list')->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect()->route('leave-types.list')->with('message',$message);
    }
    
    public function delete(Request $request, $id)
    {
        $response = $this->leaveTypeService->delete($request, $id);
        $responseData = $response->original;
        $message= $responseData['message'];
        return redirect()->route('leave-types.list')->with('message',$message);
    }
}
