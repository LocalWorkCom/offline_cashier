<?php


namespace App\Services\SettingsServices;

use App\Http\Requests\LogoFormRequest;
use App\Models\PaymentFrequency;

class PaymentFrequencyService
{

    public function index()
    {
        try {
            $lang = app()->getLocale();
            $payment_frequencies = PaymentFrequency::all();
            return ResponseWithSuccessData($lang,$payment_frequencies ,1);

        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LogoFormRequest $request)
    {
        try {
            $lang = app()->getLocale();
            $data = $request->validated();
            $payment_frequency = new PaymentFrequency();
            $payment_frequency->name_ar = $data['name_ar'];
            $payment_frequency->name_en = $data['name_en'];
            $payment_frequency->created_by = auth('admin')->id() ?? 1;
            $payment_frequency->save();

            return ResponseWithSuccessData($lang,$payment_frequency,1);
        }
        catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $lang = app()->getLocale();
            $payment_frequency = PaymentFrequency::find($id);
            return ResponseWithSuccessData($lang,$payment_frequency ,1);

        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    public function update(LogoFormRequest $request, $id)
    {
        try {
            $lang = app()->getLocale();
            $data = $request->validated();

            $payment_frequency = PaymentFrequency::findOrFail($id);
            $payment_frequency->name_ar = $data['name_ar'];
            $payment_frequency->name_en = $data['name_en'];
            $payment_frequency->modified_by = auth('admin')->id() ?? 1;

            $payment_frequency->save();

            return ResponseWithSuccessData($lang,$payment_frequency,1);
        }
        catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $lang = app()->getLocale();
            $payment_frequency = PaymentFrequency::findOrFail($id);
            $payment_frequency->deleted_by = auth('admin')->id() ?? 1;
            $payment_frequency->delete();
            return ResponseWithSuccessData($lang,$payment_frequency ,1);

        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }
}
