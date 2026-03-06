<?php

namespace App\Services\SettingsServices;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\FAQ;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\PaymentPolicyInvoiceCount;
use Illuminate\Support\Facades\Validator;

class FAQService
{
    public function index(Request $request )
    {
        $query = FAQ::whereNull('deleted_at');

        return $query;
    }


    // In your store method:
    public function store(Request $request)
    {
        $lang = app()->getLocale();
        $employee = authActionSave();
        $data = new FAQ();
        $data->name_ar = $request->name_ar;
        $data->name_en =$request->name_en;
        $data->question_ar = $request->question_ar;
        $data->question_en = $request->question_en;
        $data->answer_ar = $request->answer_ar;
        $data->answer_en = $request->answer_en;
        $data->active = $request->active;
        $data->created_by = $employee['by'];
        $data->save();
        return $data;
    }

    // In your update method:
    public function update(Request $request, $id)
    {
        $lang = app()->getLocale();

        $data = FAQ::findOrFail($id);
        $employee = authActionSave();

        if($request->has('name_ar')) {
            $data->name_ar = $request->name_ar;
        }
        if($request->has('name_en')) {
            $data->name_en = $request->name_en;
        }
        if($request->has('question_ar')) {
          $data->question_ar = $request->question_ar;
        }
        if($request->has('question_en')) {
            $data->question_en = $request->question_en;
        }
         if($request->has('answer_ar')) {
          $data->answer_ar = $request->answer_ar;
        }
        if($request->has('answer_en')) {
            $data->answer_en = $request->answer_en;
        }
        if($request->has('active')) {
            $data->active = $request->active;
        }
        $data->modified_by = $employee['by'];
        $data->save();

        return $data;
    }
    public function show($id)
    {
        return FAQ::findOrFail($id);
    }

   public function destroy($id, $lang)
    {
        try {
            $obj = FAQ::findOrFail($id);
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
            Log::error('Error deleting FAQ: ' . $e->getMessage());
            return respondError(__('Something went wrong'), 500);
        }
    }

    public function restore(Request $request, $id)
    {
        try {
            $lang = app()->getLocale();
            $obj = FAQ::withTrashed()->findOrFail($id);
            $obj->restore();

            return ResponseWithSuccessData($lang, $obj, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring FAQ: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
