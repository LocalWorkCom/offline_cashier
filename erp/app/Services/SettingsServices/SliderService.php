<?php

namespace App\Services\SettingsServices;

use App\Models\Slider;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\PaymentPolicyInvoiceCount;
use Illuminate\Support\Facades\Validator;

class SliderService
{
    public function index(Request $request)
    {
        $query = Slider::with(['dish', 'offer', 'discount'])->whereNull('deleted_at');
        return $query;
    }


    // In your store method:
    public function store(Request $request)
    {
        $lang = app()->getLocale();
        $employee = authActionSave();
        $slider = new Slider();
        $slider->name_ar = $request->name_ar;
        $slider->name_en = $request->name_en;
        $slider->description_ar = $request->description_ar;
        $slider->description_en = $request->description_en;
        $slider->flag = $request->flag;
        $slider->dish_id = $request->dish_id ?? null;
        $slider->offer_id = $request->offer_id ?? null;
        $slider->discount_id = $request->discount_id ?? null;
        $slider->created_by = $employee['by'];

        $slider->save();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            UploadFile('images/Sliders', 'image', $slider, $image);
        }

        // Refresh the model to get any relationships or computed attributes
        $slider->refresh();

        // Return the stored slider data
        return $slider;
    }

    // In your update method:
    public function update(Request $request, $id)
    {
        $lang = app()->getLocale();
        $employee = authActionSave();
        $slider = Slider::findOrFail($id);
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            UploadFile('images/Sliders', 'image', $slider, $image);
        }
        if ($request->has('name_ar')) {
            $slider->name_ar = $request->name_ar;
        }
        if ($request->has('name_en')) {
            $slider->name_en = $request->name_en;
        }
        if ($request->has('description_ar')) {
            $slider->description_ar = $request->description_ar;
        }
        if ($request->has('description_en')) {
            $slider->description_en = $request->description_en;
        }
        if ($request->has('flag')) {
            $slider->flag = $request->flag;
        }
        if ($request->has('dish_id')) {
            $slider->dish_id = $request->dish_id;
        }
        if ($request->has('offer_id')) {
            $slider->offer_id = $request->offer_id;
        }
        if ($request->has('discount_id')) {
            $slider->discount_id = $request->discount_id;
        }
        if (!$request->has('dish_id')) {
            $slider->dish_id = null;
        }
        if (!$request->has('offer_id')) {
            $slider->offer_id = null;
        }
        if (!$request->has('discount_id')) {
            $slider->discount_id = null;
        }
        $slider->modified_by = $employee['by'];
        $slider->save();


        return $slider;
    }
    public function show($id)
    {
        $slider = Slider::with(['dish', 'offer'])
            ->findOrFail($id);

        $slider = collect($slider)->filter(function ($value) {
            return !is_null($value);
        });
        return $slider;
        // return Slider::with(['dish', 'offer'])->findOrFail($id);
    }

    public function destroy($id, $lang)
    {
        try {
            $slider = Slider::findOrFail($id);
            $slider->update([
                'deleted_by' => authActionSave()['by']
            ]);

            // Perform soft delete
            $slider->delete();

            // Success response
            return RespondWithSuccessRequest($lang, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return  RespondWithBadRequestNotExist();
        } catch (\Exception $e) {
            Log::error('Error deleting Slider: ' . $e->getMessage());
            return respondError(__('Something went wrong'), 500);
        }
    }

    public function restore(Request $request, $id)
    {
        try {
            $lang = app()->getLocale();
            $obj = Slider::withTrashed()->findOrFail($id);
            $obj->restore();

            return ResponseWithSuccessData($lang, $obj, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring Slider: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
