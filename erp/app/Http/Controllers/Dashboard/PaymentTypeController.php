<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogoFormRequest;
use App\Models\PaymentType;
use Illuminate\Http\Request;
class PaymentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payment_types = PaymentType::all();
        return view('dashboard.payment_type.list', compact('payment_types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LogoFormRequest $request)
    {
        $lang = app()->getLocale();
        $data = $request->validated();

        $payment_type = new PaymentType();
        $payment_type->name_ar = $data['name_ar'];
        $payment_type->name_en = $data['name_en'];
        $payment_type->created_by = auth('admin')->id() ?? 1;

        $payment_type->save();

        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/payment-types')->with('message', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $payment_type = PaymentType::find($id);

        if (!$payment_type) {
            return response()->json(['error' => 'Payment type not found'], 404);
        }

        return response()->json([
            'name_ar' => $payment_type->name_ar,
            'name_en' => $payment_type->name_en,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LogoFormRequest $request, string $id)
    {
        $lang = app()->getLocale();
        $data = $request->validated();

        $payment_type = PaymentType::findOrFail($id);
        $payment_type->name_ar = $data['name_ar'];
        $payment_type->name_en = $data['name_en'];
        $payment_type->modified_by = auth('admin')->id() ?? 1;

        $payment_type->save();

        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/payment-types')->with('message', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lang = app()->getLocale();
        $payment_type = PaymentType::findOrFail($id);
        $payment_type->deleted_by = auth('admin')->id() ?? 1;
        $payment_type->delete();

        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/payment-types')->with('message', $message);
    }
}
