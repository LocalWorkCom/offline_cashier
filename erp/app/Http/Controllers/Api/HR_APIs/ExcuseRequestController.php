<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExcuseRequest;
use App\Models\Excuse;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ExcuseRequestController extends Controller
{
    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $excuseRequests = ExcuseRequest::with('employee', 'approver')->get();
            return ResponseWithSuccessData($lang, $excuseRequests, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching excuse requests: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            $excuseRequest = ExcuseRequest::with('employee', 'approver')->findOrFail($id);
            return ResponseWithSuccessData($lang, $excuseRequest, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching excuse request: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function store(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'reason' => 'nullable|string',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'date' => 'date',
            ]);
    
            // Fetch the employee and calculate requested hours
            $employee = Employee::findOrFail($request->employee_id);
            $start = new \DateTime($request->start_time);
            $end = new \DateTime($request->end_time);
            $requestedHours = $start->diff($end)->h;
    
            // Check if the requested hours exceed the limits
            if ($employee->daily_excuse_hours < $requestedHours) {
                return RespondWithBadRequestData($lang, 'Exceeds daily excuse hour limit.');
            }
            if ($employee->monthly_excuse_hours < $requestedHours) {
                return RespondWithBadRequestData($lang, 'Exceeds monthly excuse hour limit.');
            }
    
            $excuseRequest = ExcuseRequest::create([
                'employee_id' => $request->employee_id,
                'reason' => $request->reason,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'status' => 'pending',
                'date' => $request->input('date', now()->toDateString()),
            ]);
    
            return ResponseWithSuccessData($lang, $excuseRequest, 1);
        } catch (\Exception $e) {
            Log::error('Error creating excuse request: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            $excuseRequest = ExcuseRequest::findOrFail($id);
    
            if ($excuseRequest->status !== 'pending') {
                return RespondWithBadRequestData($lang, 'Only pending requests can be updated.');
            }
    
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'reason' => 'nullable|string',
            ]);
    
            // Fetch the employee and calculate requested hours
            $employee = Employee::findOrFail($request->employee_id);
            $start = new \DateTime($request->start_time);
            $end = new \DateTime($request->end_time);
            $requestedHours = $start->diff($end)->h;
    
            // Check if the requested hours exceed the limits
            if ($employee->daily_excuse_hours < $requestedHours) {
                return RespondWithBadRequestData($lang, 'Exceeds daily excuse hour limit.');
            }
            if ($employee->monthly_excuse_hours < $requestedHours) {
                return RespondWithBadRequestData($lang, 'Exceeds monthly excuse hour limit.');
            }
    
            $excuseRequest->update([
                'employee_id' => $request->employee_id,
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'reason' => $request->reason,
            ]);
    
            return ResponseWithSuccessData($lang, $excuseRequest, 1);
    
        } catch (\Exception $e) {
            Log::error('Error updating excuse request: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    
     

    public function approve(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            $excuseRequest = ExcuseRequest::findOrFail($id);
    
            $excuseRequest->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_date' => now()->toDateString(),
            ]);
    
            Excuse::create([
                'employee_id' => $excuseRequest->employee_id,
                'reason' => $excuseRequest->reason,
                'date' => $excuseRequest->date,
                'start_time' => $excuseRequest->start_time,
                'end_time' => $excuseRequest->end_time,
                'requested_time' => $excuseRequest->created_at->format('H:i'),
                'status' => 'approved',
                'approver_id' => Auth::id(),
                'approved_date' => now()->toDateString(),
                'is_paid' => $request->input('is_paid', false),
                'created_by' => Auth::id(),
                'excuse_request_id' => $excuseRequest->id, 
            ]);
    
            return ResponseWithSuccessData($lang, $excuseRequest, 1);
        } catch (\Exception $e) {
            Log::error('Error approving excuse request: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    
    public function reject(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            $excuseRequest = ExcuseRequest::findOrFail($id);
    
            $excuseRequest->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_date' => now(),
            ]);
    
            Excuse::create([
                'employee_id' => $excuseRequest->employee_id,
                'reason' => $excuseRequest->reason,
                'date' => $excuseRequest->date,
                'start_time' => $excuseRequest->start_time,
                'end_time' => $excuseRequest->end_time,
                'requested_time' => $excuseRequest->created_at->format('H:i'),
                'status' => 'rejected',
                'approver_id' => Auth::id(),
                'approved_date' => now()->toDateString(),
                'rejection_reason' => $request->input('rejection_reason', 'Not specified'),
                'is_paid' => $request->input('is_paid', false),
                'created_by' => Auth::id(),
                'excuse_request_id' => $excuseRequest->id, // Linking excuse_request_id
            ]);
    
            return ResponseWithSuccessData($lang, $excuseRequest, 1);
        } catch (\Exception $e) {
            Log::error('Error rejecting excuse request: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    
    public function pendingRequests(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $pendingExcuseRequests = ExcuseRequest::with('employee', 'approver')
                ->where('status', 'pending')
                ->get();
    
            return ResponseWithSuccessData($lang, $pendingExcuseRequests, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching pending excuse requests: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    
public function cancel(Request $request, $id)
{
    try {
        $lang = $request->header('lang', 'en');
        $excuseRequest = ExcuseRequest::findOrFail($id);

        if ($excuseRequest->status !== 'pending') {
            return RespondWithBadRequestData($lang, 'Only pending requests can be canceled.');
        }

        $excuseRequest->update([
            'status' => 'canceled',
        ]);

        $excuseRequest->delete();

        return ResponseWithSuccessData($lang, $excuseRequest, 'Request successfully canceled.');
    } catch (\Exception $e) {
        Log::error('Error canceling excuse request: ' . $e->getMessage());
        return RespondWithBadRequestData($lang, 2);
    }
}

public function restore(Request $request, $id)
{
    try {
        $lang = $request->header('lang', 'en');
        $excuseRequest = ExcuseRequest::withTrashed()->findOrFail($id);

        if ($excuseRequest->status !== 'canceled') {
            return RespondWithBadRequestData($lang, 'Only canceled requests can be restored.');
        }

        $excuseRequest->restore();

        $excuseRequest->update([
            'status' => 'pending',
        ]);

        return ResponseWithSuccessData($lang, $excuseRequest, 'Request successfully restored.');
    } catch (\Exception $e) {
        Log::error('Error restoring excuse request: ' . $e->getMessage());
        return RespondWithBadRequestData($lang, 2);
    }
}

}
