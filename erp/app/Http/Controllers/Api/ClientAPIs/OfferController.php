<?php

namespace App\Http\Controllers\Api\ClientAPIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfferResource;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\isEmpty;

class OfferController extends Controller
{
    private $lang;

    public function __construct(Request $request)
    {
        $this->lang = $request->header('lang', 'ar');
        if (!CheckToken()) {
            return RespondWithBadRequest($this->lang, 5);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $offers = Offer::with('details')->whereHas('details')->where('is_active',1)->get() ?? collect();
        $offers = Offer::with('details', 'branch')->get() ?? collect();


        $filteredOffers = $offers->filter(function ($offer) {
            return $offer->details->every(function ($detail) {
                return !is_null($detail->getTypeName($this->lang));
            });
        });

        if(!$filteredOffers){
            return RespondWithBadRequestData($this->lang, 2);
        }
        return ResponseWithSuccessData($this->lang, OfferResource::collection($filteredOffers), 1);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function save(Request $request, string $id = null)
    {
        $messages = [];

        if (app()->getLocale() == 'ar') {
            $messages = [
                'branches.required_if' => 'حقل الفروع مطلوب عند تحديد "اختر" في اختيار الفرع.',
                'branches.*.exists' => 'الفرع المحدد غير موجود.',
                'end_date.after_or_equal' => 'يجب أن يكون تاريخ الانتهاء بعد أو يساوي تاريخ البدء.',
            ];
        } else {
            $messages = [
                'branches.required_if' => 'The branches field is required when the branch selection is specific.',
                'branches.*.exists' => 'The selected branch does not exist.',
                'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            ];
        }
        $data = $request->validate([
            'branch_selection' => 'required|in:all,specific',
            'branches' => 'required_if:branch_selection,specific|array',
            'branches.*' => 'exists:branches,id',
            'name_ar' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request, $id) {
                    $exists = Offer::where('name_ar', $value)
                        ->where('start_date', '<=', $request->end_date)
                        ->where('end_date', '>=', $request->start_date)
                        ->where('is_active', 1)
                        ->when($id, function ($query) use ($id) {
                            $query->where('id', '!=', $id); // Exclude current record in case of update
                        })
                        ->exists();

                    if ($exists) {
                        $fail(__('validation.unique_within_duration', ['attribute' => __('validation.attributes.name_ar')]));
                    }
                },
            ],
            'name_en' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request, $id) {
                    $exists = Offer::where('name_en', $value)
                        ->where('start_date', '<=', $request->end_date)
                        ->where('end_date', '>=', $request->start_date)
                        ->where('is_active', 1)
                        ->when($id, function ($query) use ($id) {
                            $query->where('id', '!=', $id); // Exclude current record in case of update
                        })
                        ->exists();

                    if ($exists) {
                        $fail(__('validation.unique_within_duration', ['attribute' => __('validation.attributes.name_en')]));
                    }
                },
            ],
            'discount_type' => 'required|string|in:fixed,percentage',
            'discount_value' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->discount_type === 'percentage' && $value > 100) {
                        $fail(__('validation.discount_exceeds_100'));
                    }
                },
            ],
            'description_ar' => 'required|string',
            'description_en' => 'required|string',
            'image_ar' => 'nullable|max:2048',
            'image_en' => 'nullable|max:2048',
            'is_active' => [
                'required',
                'in:0,1',
                function ($attribute, $value, $fail) use ($request, $id) {
                    if ($value == 1) { // Only check if activating the offer
                        $exists = Offer::where(function ($query) use ($request, $id) {
                            $query->where('name_ar', $request->name_ar)
                                ->orWhere('name_en', $request->name_en);
                        })
                            ->where('is_active', 1)
                            ->when($id, function ($query) use ($id) {
                                $query->where('id', '!=', $id); // Exclude current record in case of update
                            })
                            ->exists();

                        if ($exists) {
                            $fail(__('validation.active_offer_conflict'));
                        }
                    }
                },
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ], $messages);

        if ($request->hasFile('image_ar')) {
            $file = $request->file('image_ar');
            $newFileName = 'image_ar_' . rand(1, 999999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/offers/ar'), $newFileName);
            $data['image_ar'] = url('images/offers/ar/' . $newFileName);
        }

        if ($request->hasFile('image_en')) {
            $file = $request->file('image_en');
            $newFileName = 'image_en_' . rand(1, 999999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/offers/en'), $newFileName);
            $data['image_en'] = url('images/offers/en/' . $newFileName);
        }
        $id == null ?$data['created_by'] =Auth::guard('api')->user()->id??1
            : $data['modified_by'] =Auth::guard('api')->user()->id??1;

        $offer = Offer::updateOrCreate(['id' => $id], $data);

        return ResponseWithSuccessData($this->lang, OfferResource::make($offer), 1);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // $offer = Offer::with('details')->whereHas('details')->where('is_active',1)->find($id) ?? collect();
        $offer = Offer::with('details')
            // ->whereHas('details')
            // ->where('is_active', 1)
            ->where('id', $id)
            ->first();

        // $filteredOffer = $offer->filter(function ($offer) {
        //     return $offer->details->every(function ($detail) {
        //         return !is_null($detail->getTypeName($this->lang));
        //     });
        // });
        if (!$offer) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        // if (!$filteredOffer) {
        //     return RespondWithBadRequestData($this->lang, 2);
        // }
        return ResponseWithSuccessData($this->lang, $offer ? OfferResource::make($offer) : [], 1);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Offer::find($id);

        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        $data->deleted_by = Auth::guard('api')->user()->id ?? 1;
        $data->save();
        $data->delete();

        return ResponseWithSuccessData($this->lang, OfferResource::make($data), 1);
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        $data = Offer::withTrashed()->find($id);

        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        $data->restore();
        return ResponseWithSuccessData($this->lang, OfferResource::make($data), 1);
    }
}
