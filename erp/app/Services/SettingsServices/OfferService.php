<?php

namespace App\Services\SettingsServices;

use App\Http\Resources\OfferResource;
use App\Models\Branch;
use App\Models\Offer;
use App\Models\OfferDetail;
use App\Models\Slider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OfferService
{
    protected $lang;

    public function __construct()
    {
        $this->lang = app()->getLocale();
    }

    /**
     * Display a listing of offers
     */
    public function index()
    {
        $offers = Offer::with('details')->get();
        return ResponseWithSuccessData($this->lang, OfferResource::collection($offers), 1);
    }

    /**
     * Store or update an offer
     */
    public function save(Request $request, $id = null)
    {
        $validator = $this->validateOfferData($request, $id);

        if ($validator->fails()) {
            return RespondWithBadRequestData($this->lang, $validator->errors()->first());
        }

        $data = $this->processOfferData($request, $validator->validated(), $id);
        $offer = Offer::updateOrCreate(['id' => $id], $data);

        return ResponseWithSuccessData($this->lang, OfferResource::make($offer), 1);
    }

    /**
     * Validate offer data
     */
    protected function validateOfferData(Request $request, $id = null)
    {
        $messages = [
            'ar' => [
                'branches.required_if' => 'حقل الفروع مطلوب عند تحديد "اختر" في اختيار الفرع.',
                'branches.*.exists' => 'الفرع المحدد غير موجود.',
                'end_date.after_or_equal' => 'يجب أن يكون تاريخ الانتهاء بعد أو يساوي تاريخ البدء.',
            ],
            'en' => [
                'branches.required_if' => 'The branches field is required when the branch selection is specific.',
                'branches.*.exists' => 'The selected branch does not exist.',
                'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            ]
        ];

        return validator($request->all(), [
            'branch_selection' => 'required|in:all,specific',
            'branches' => 'required_if:branch_selection,specific|array',
            'branches.*' => 'exists:branches,id',
            'name_ar' => $this->getNameValidationRules($request, $id, 'name_ar'),
            'name_en' => $this->getNameValidationRules($request, $id, 'name_en'),
            'discount_type' => 'required|string|in:fixed,percentage',
            'discount_value' => $this->getDiscountValidationRules($request),
            'description_ar' => 'required|string',
            'description_en' => 'required|string',
            'image_ar' => 'nullable|max:2048',
            'image_en' => 'nullable|max:2048',
            'is_active' => $this->getActiveStatusValidationRules($request, $id),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ], $messages[$this->lang] ?? $messages['en']);
    }

    /**
     * Get name validation rules
     */
    protected function getNameValidationRules(Request $request, $id, $attribute)
    {
        return [
            'required',
            'string',
            function ($attribute, $value, $fail) use ($request, $id) {
                $exists = Offer::where($attribute, $value)
                    ->where('is_active', 1)
                    ->where('start_date', '<=', $request->end_date)
                    ->where('end_date', '>=', $request->start_date)
                    ->when($id, fn($query) => $query->where('id', '!=', $id))
                    ->exists();

                if ($exists) {
                    $fail(__('validation.unique_within_duration', ['attribute' => __('validation.attributes.' . $attribute)]));
                }
            },
        ];
    }

    /**
     * Get discount validation rules
     */
    protected function getDiscountValidationRules(Request $request)
    {
        return [
            'required',
            'numeric',
            function ($attribute, $value, $fail) use ($request) {
                if ($request->discount_type === 'percentage' && $value > 100) {
                    $fail(__('validation.discount_exceeds_100'));
                }
            },
        ];
    }

    /**
     * Get active status validation rules
     */
    protected function getActiveStatusValidationRules(Request $request, $id)
    {
        return [
            'required',
            'in:0,1',
            function ($attribute, $value, $fail) use ($request, $id) {
                if ($value == 1) {
                    $exists = Offer::where(fn($query) => $query->where('name_ar', $request->name_ar)
                        ->orWhere('name_en', $request->name_en))
                        ->where('is_active', 1)
                        ->when($id, fn($query) => $query->where('id', '!=', $id))
                        ->exists();

                    if ($exists) {
                        $fail(__('validation.active_offer_conflict'));
                    }
                }
            },
        ];
    }

    /**
     * Process offer data before saving
     */
    protected function processOfferData(Request $request, array $data, $id = null)
    {
        // Handle image uploads
        $data['image_ar'] = $this->handleImageUpload($request->file('image_ar'), 'ar', $id);
        $data['image_en'] = $this->handleImageUpload($request->file('image_en'), 'en', $id);

        // Set created/modified by
        $data[$id ? 'modified_by' : 'created_by'] = Auth::guard('api')->id() ?? 1;

        // Handle branch logic
        $data['branch_id'] = $request->branch_selection === 'all'
            ? '-1'
            : implode(',', $request->branches);

        return $data;
    }

    /**
     * Handle image upload
     */
    protected function handleImageUpload($file, $lang, $id = null)
    {
        if (!$file) {
            $offer = $id ? Offer::find($id) : null;
            if ($offer) {
                return $lang === 'ar' ? $offer->image_ar : $offer->image_en;
            }
            return null;
        }

        $newFileName = 'image_' . $lang . '_' . rand(1, 999999) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path("images/offers/{$lang}"), $newFileName);

        return url("images/offers/{$lang}/{$newFileName}");
    }

    /**
     * Display the specified offer
     */
    public function show(string $id)
    {
        $offer = Offer::find($id);

        if (!$offer) {
            return RespondWithBadRequestData($this->lang, 2);
        }

        $branchIds = explode(',', $offer->branch_id);
        $branches = Branch::whereIn('id', $branchIds)->get();

        return ResponseWithSuccessData($this->lang, [
            'offer' => $offer,
            'branches' => $branches
        ], 1);
    }

    /**
     * Delete an offer
     */
    public function destroy(string $id)
    {
        $offer = Offer::find($id);

        if (!$offer) {
            return RespondWithBadRequestData($this->lang, 2);
        }

        // Delete associated sliders
        if ($offer->slider->isNotEmpty()) {
            Slider::where('offer_id', $id)->delete();
        }

        $offer->update([
            'deleted_by' => Auth::guard('api')->id() ?? 1
        ]);

        $offer->delete();

        return ResponseWithSuccessData($this->lang, OfferResource::make($offer), 1);
    }

    /**
     * Restore a deleted offer
     */
    public function restore(string $id)
    {
        $offer = Offer::withTrashed()->find($id);

        if (!$offer) {
            return RespondWithBadRequestData($this->lang, 2);
        }

        $offer->restore();
        return ResponseWithSuccessData($this->lang, OfferResource::make($offer), 1);
    }

    /**
     * List offer details
     */
    public function listDetail($offerId)
    {
        $offer = Offer::with(['offerDetails.detail' => fn($query) => $query->select('id', 'name_ar')])
            ->findOrFail($offerId);

        return ResponseWithSuccessData($this->lang, $offer, 1);
    }

    /**
     * Save offer details
     */
    public function saveOfferDetails(Request $request, $offerId)
    {
        $validated = $request->validate([
            'details.*.detail_id' => 'required|exists:details,id',
            'offer_detail_id' => 'nullable|array',
            'offer_detail_id.*' => 'nullable|integer|exists:offer_details,id',
        ]);

        $offerDetailIds = $request->offer_detail_id ?? [];
        $details = $request->details ?? [];

        // Check for duplicate details
        $detailIds = collect($details)->pluck('detail_id');
        if ($detailIds->count() !== $detailIds->unique()->count()) {
            return CustomRespondWithBadRequest('Duplicate details are submitted.');
        }

        $this->syncOfferDetails($offerId, $offerDetailIds, $details);

        return RespondWithSuccessRequest($this->lang, 1);
    }

    /**
     * Sync offer details
     */
    protected function syncOfferDetails($offerId, $offerDetailIds, $details)
    {
        // Delete removed details
        OfferDetail::where('offer_id', $offerId)
            ->whereNotIn('id', $offerDetailIds)
            ->delete();

        // Update or create details
        foreach ($details as $index => $detail) {
            $detailId = (int) $detail['detail_id'];
            $offerDetailId = $offerDetailIds[$index] ?? null;

            if ($this->detailExistsForOffer($offerId, $detailId, $offerDetailId)) {
                throw ValidationException::withMessages([
                    'details' => "The detail with ID {$detailId} already exists for this offer."
                ]);
            }

            $data = [
                'detail_id' => $detailId,
                'offer_id' => $offerId,
                'created_by' => Auth::guard('admin')->id(),
            ];

            if ($offerDetailId) {
                OfferDetail::updateOrCreate(['id' => $offerDetailId], $data);
            } else {
                OfferDetail::create($data);
            }
        }
    }

    /**
     * Check if detail exists for offer
     */
    protected function detailExistsForOffer($offerId, $detailId, $excludeId = null)
    {
        return OfferDetail::where('offer_id', $offerId)
            ->where('detail_id', $detailId)
            ->when($excludeId, fn($query) => $query->where('id', '!=', $excludeId))
            ->exists();
    }
}
