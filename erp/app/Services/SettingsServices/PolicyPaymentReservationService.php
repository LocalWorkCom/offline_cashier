<?php

namespace App\Services\SettingsServices;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\PaymentPolicyInvoiceCount;
use App\Models\PolicyPaymentReservation;
use Illuminate\Support\Facades\Validator;

class PolicyPaymentReservationService
{
    public function index(Request $request )
    {
        $query = PolicyPaymentReservation::whereNull('deleted_at');

        return $query;
    }


    // In your store method:
    public function store(Request $request)
    {
        $lang = app()->getLocale();
        $employee = authActionSave();
        $data = new PolicyPaymentReservation();
        $data->payment_ar = $request->payment_ar;
        $data->payment_en =$request->payment_en;
        $data->reservation_ar = $request->reservation_ar;
        $data->reservation_en = $request->reservation_en;
        $data->created_by = $employee['by'];
        $data->save();
        return $data;
    }

    // In your update method:
    public function update(Request $request, $id)
    {
        $lang = app()->getLocale();

        $data = PolicyPaymentReservation::findOrFail($id);
        $employee = authActionSave();

        if($request->has('payment_ar')) {
           $data->payment_ar = $request->payment_ar;
        }
        if($request->has('payment_en')) {
            $data->payment_en =$request->payment_en;
        }
        if($request->has('reservation_ar')) {
            $data->reservation_ar = $request->reservation_ar;
        }
        if($request->has('reservation_en')) {
            $data->reservation_en = $request->reservation_en;
        }

        $data->updated_by = $employee['by'];
        $data->save();

        return $data;
    }
    public function show($id)
    {
        return PolicyPaymentReservation::findOrFail($id);
    }

   public function destroy($id, $lang)
    {
        try {
            $obj = PolicyPaymentReservation::findOrFail($id);
            $obj->update([
                'deleted_by' => authActionSave()['by'],
                'deleted_by_type' => authActionSave()['type']
            ]);

            // Perform soft delete
            $obj->delete();

            // Success response
            return RespondWithSuccessRequest($lang, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return  RespondWithBadRequestNotExist();
        } catch (\Exception $e) {
            Log::error('Error deleting Return Policy: ' . $e->getMessage());
            return respondError(__('Something went wrong'), 500);
        }
    }

    public function restore(Request $request, $id)
    {
        try {
            $lang = app()->getLocale();
            $obj = PolicyPaymentReservation::withTrashed()->findOrFail($id);
            $obj->restore();

            return ResponseWithSuccessData($lang, $obj, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring Return Policy: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
