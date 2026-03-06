<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PolicyPaymentReservation;
use App\Services\SettingsServices\PaymentReservationPolicyService;
use Illuminate\Http\Request;

class PaymentReservationPolicyController extends Controller
{
    protected $paymentReservationPolicyService;

    public function __construct(PaymentReservationPolicyService $paymentReservationPolicyService)
    {
        $this->paymentReservationPolicyService = $paymentReservationPolicyService;
    }
    public function index()
    {
        $response = $this->paymentReservationPolicyService->index();

        $responseData = $response->original;

        $data = $responseData['data'];

        return view('dashboard.paymentReservationPolicy.index', compact('data'));
    }
    public function update(Request $request)
    {
        $response = $this->paymentReservationPolicyService->update($request);

        $responseData = $response->original;

        $data = $responseData['data'];

        return redirect()->back()->with('success', 'Data updated successfully');
    }

}
