<?php

namespace App\Services\SettingsServices;

use App\Models\Logo;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\PaymentPolicyInvoiceCount;
use Illuminate\Support\Facades\Validator;

class LogoService
{
    public function index(Request $request )
    {
        $query = Logo::whereNull('deleted_at');

        return $query;
    }


    // In your store method:
    public function store(Request $request)
    {
        $lang = app()->getLocale();
        $employee = authActionSave();
        $obj = new Logo();
        $obj->name_ar = $request->name_ar;
        $obj->name_en = $request->name_en;
        $obj->created_by = $employee['by'];
        $obj->save();
         if ($request->hasFile('image')) {
            $image = $request->file('image');
            UploadFile('images/logos', 'image', $obj, $image);
        }

        return $obj;
    }

    // In your update method:
    public function update(Request $request, $id)
    {
        $lang = app()->getLocale();

        $obj = Logo::findOrFail($id);
        $employee = authActionSave();
      if($request->hasFile('image')) {
            $image = $request->file('image');
            UploadFile('images/logos', 'image', $obj, $image);
        }
        if($request->has('name_ar')) {
            $obj->name_ar = $request->name_ar;
        }
        if($request->has('name_en')) {
            $obj->name_en = $request->name_en;
        }
        $obj->modified_by = $employee['by'];
        $obj->save();

        return $obj;
    }
    public function show($id)
    {
        return Logo::findOrFail($id);
    }

   public function destroy($id, $lang)
    {
        try {
            $obj = Logo::findOrFail($id);
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
            Log::error('Error deleting logo: ' . $e->getMessage());
            return respondError(__('Something went wrong'), 500);
        }
    }

    public function restore(Request $request, $id)
    {
        try {
            $lang = app()->getLocale();
            $obj = Logo::withTrashed()->findOrFail($id);
            $obj->restore();

            return ResponseWithSuccessData($lang, $obj, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring logo: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
