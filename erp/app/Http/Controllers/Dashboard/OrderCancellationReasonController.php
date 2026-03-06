<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\OrderCancellationReason;
use App\Services\SettingsServices\OrderCancellationReasonService;
use Illuminate\Http\Request;

class OrderCancellationReasonController extends Controller
{
    protected $orderCancellationReasonService;
    public function __construct(OrderCancellationReasonService $orderCancellationReasonService)
    {
        $this->orderCancellationReasonService = $orderCancellationReasonService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reasons = $this->orderCancellationReasonService->index()->get();
        return view('dashboard.cancel_reasons.index', compact('reasons'));
    }

    // Store a newly created reason
    public function store(Request $request)
    {
        $response = $this->orderCancellationReasonService->store($request->all());
        $responseData = $response->original;
        $reason = $responseData['data'];
        return redirect()->back()->with('success', 'Reason added successfully.');
    }

    // Update an existing reason
    public function update(Request $request, $id)
    {
        $response = $this->orderCancellationReasonService->update($request->all(), $id);


        return redirect()->back()->with('success', 'Reason updated successfully.');
    }

    // Delete a reason
    public function destroy($id)
    {
        $response = $this->orderCancellationReasonService->destroy($id);
      

        return response()->json([
            'status' => true,
            'message' => 'Reason deleted successfully.',
        ]);
    }
}
