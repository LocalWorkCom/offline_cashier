<?php

namespace App\Services\SettingsServices;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TermsAndCondition;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\PaymentPolicyInvoiceCount;
use Illuminate\Support\Facades\Validator;

class TermsAndConditionsService
{
    public function index(Request $request )
    {
        $query = TermsAndCondition::whereNull('deleted_at');

        return $query;
    }


    // In your store method:
    public function store(Request $request)
    {
        $lang = app()->getLocale();
        $employee = authActionSave();
        $term = new TermsAndCondition();
        $term->name_ar = $request->name_ar;
        $term->name_en =$request->name_en;
        $term->description_ar = $request->description_ar;
        $term->description_en = $request->description_en;
        $term->active = $request->active;
        $term->created_by = $employee['by'];
        $term->save();
        return $term;
    }

    // In your update method:
    public function update(Request $request, $id)
    {
        $lang = app()->getLocale();

        $term = TermsAndCondition::findOrFail($id);
        $employee = authActionSave();

        if($request->has('name_ar')) {
            $term->name_ar = $request->name_ar;
        }
        if($request->has('name_en')) {
            $term->name_en = $request->name_en;
        }
        if($request->has('description_ar')) {
            $term->description_ar = $request->description_ar;
        }
        if($request->has('description_en')) {
            $term->description_en = $request->description_en;
        }
        if($request->has('active')) {
            $term->active = $request->active;
        }
        $term->modified_by = $employee['by'];
        $term->save();

        return $term;
    }
    public function show($id)
    {
        return TermsAndCondition::findOrFail($id);
    }

   public function destroy($id, $lang)
    {
        try {
            $obj = TermsAndCondition::findOrFail($id);
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
            Log::error('Error deleting terms and conditions: ' . $e->getMessage());
            return respondError(__('Something went wrong'), 500);
        }
    }

    public function restore(Request $request, $id)
    {
        try {
            $lang = app()->getLocale();
            $obj = TermsAndCondition::withTrashed()->findOrFail($id);
            $obj->restore();

            return ResponseWithSuccessData($lang, $obj, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring terms and conditions:: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
