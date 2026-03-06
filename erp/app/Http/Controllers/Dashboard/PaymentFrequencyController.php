<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogoFormRequest;
use App\Models\PaymentFrequency;
use App\Services\SettingsServices\PaymentFrequencyService;
use Illuminate\Http\Request;
class PaymentFrequencyController extends Controller
{
    protected $paymentFrequencyService;

    public function __construct(PaymentFrequencyService $paymentFrequencyService)
    {
        $this->paymentFrequencyService = $paymentFrequencyService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $response = $this->paymentFrequencyService->index();
        $responseData = $response->original;
        $payment_frequencies = $responseData['data'];
        return view('dashboard.payment_frequency.list', compact('payment_frequencies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LogoFormRequest $request)
    {
        $response = $this->paymentFrequencyService->store($request);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/payment-frequencies')->with('message', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $response = $this->paymentFrequencyService->show($id);
        $responseData = $response->original;
        $payment_frequency = $responseData['data'];

        if (!$payment_frequency) {
            return response()->json(['error' => 'Payment type not found'], 404);
        }

        return response()->json([
            'name_ar' => $payment_frequency->name_ar,
            'name_en' => $payment_frequency->name_en,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LogoFormRequest $request, string $id)
    {
        $response = $this->paymentFrequencyService->update($request, $id);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/payment-frequencies')->with('message', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = $this->paymentFrequencyService->destroy($id);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/payment-frequencies')->with('message', $message);
    }
}
