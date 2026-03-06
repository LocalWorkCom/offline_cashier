<?php

namespace App\Services\SettingsServices;

use App\Http\Resources\OfferDetailResource;
use App\Models\Dish;
use App\Models\OfferDetail;
use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class OfferDetailService
{
    private $lang;

    public function __construct(Request $request)
    {
        $this->lang = $request->header('lang', 'ar');
    }

    /**
     * Get all offer details for a specific offer
     */
    public function index($offerId)
    {
        $details = OfferDetail::where('offer_id', $offerId)
            ->with(['dish', 'addon', 'product'])
            ->get();

        return ResponseWithSuccessData($this->lang, OfferDetailResource::collection($details), 1);
    }

    /**
     * Create or update an offer detail
     */
    public function save(Request $request, string $id = null)
    {
        $data = $this->validateRequest($request);
        $this->setAuditFields($data, $id);

        $offerDetail = OfferDetail::updateOrCreate(['id' => $id], $data);

        return ResponseWithSuccessData($this->lang, OfferDetailResource::make($offerDetail), 1);
    }

    /**
     * Validate the request data
     */
    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'offer_id' => 'required|numeric|exists:offers,id',
            'offer_type' => 'required|string|in:dishes,products,addons',
            'type_id' => [
                'required',
                'numeric',
                $this->getTypeIdValidationRule($request),
            ],
            'count' => 'required|numeric|min:1',
        ]);
    }

    /**
     * Get validation rule for type_id based on offer_type
     */
    protected function getTypeIdValidationRule(Request $request): callable
    {
        return function ($attribute, $value, $fail) use ($request) {
            $offerType = $request->input('offer_type');
            $model = $this->getModelForOfferType($offerType);

            if (!$model::where('id', $value)->exists()) {
                $message = $this->getTypeIdErrorMessage($offerType);
                $fail($message);
            }
        };
    }

    /**
     * Get the appropriate model for the offer type
     */
    protected function getModelForOfferType(string $offerType): string
    {
        return match ($offerType) {
            'products' => Product::class,
            'dishes' => Dish::class,
            'addons' => Recipe::class,
            default => throw new \InvalidArgumentException('Invalid offer type provided'),
        };
    }

    /**
     * Get the appropriate error message for type_id validation
     */
    protected function getTypeIdErrorMessage(string $offerType): string
    {
        $messages = [
            'ar' => [
                'products' => 'كود المنتج غير موجود',
                'dishes' => 'كود الطبق غير موجود',
                'addons' => 'كود الإضافة غير موجود',
            ],
            'en' => [
                'products' => 'The selected type_id is invalid for products.',
                'dishes' => 'The selected type_id is invalid for dishes.',
                'addons' => 'The selected type_id is invalid for addons.',
            ],
        ];

        return $messages[$this->lang][$offerType] ?? $messages['en'][$offerType];
    }

    /**
     * Set created_by or modified_by fields
     */
    protected function setAuditFields(array &$data, ?string $id): void
    {
        $field = $id ? 'modified_by' : 'created_by';
        $data[$field] = Auth::guard('api')->id() ?? 1;
    }

    /**
     * Get a specific offer detail
     */
    public function show(string $id)
    {
        $offerDetail = $this->findOfferDetail($id);

        if (!$offerDetail) {
            return RespondWithBadRequestData($this->lang, 2);
        }

        return ResponseWithSuccessData($this->lang, OfferDetailResource::make($offerDetail), 1);
    }

    /**
     * Delete an offer detail
     */
    public function destroy(string $id)
    {
        $offerDetail = $this->findOfferDetail($id);

        if (!$offerDetail) {
            return RespondWithBadRequestData($this->lang, 2);
        }

        $this->softDeleteOfferDetail($offerDetail);

        return ResponseWithSuccessData($this->lang, OfferDetailResource::make($offerDetail), 1);
    }

    /**
     * Restore a deleted offer detail
     */
    public function restore(string $id)
    {
        $offerDetail = OfferDetail::withTrashed()->find($id);

        if (!$offerDetail) {
            return RespondWithBadRequestData($this->lang, 2);
        }

        $offerDetail->restore();
        return ResponseWithSuccessData($this->lang, OfferDetailResource::make($offerDetail), 1);
    }

    /**
     * Find an offer detail by ID
     */
    protected function findOfferDetail(string $id): ?OfferDetail
    {
        return OfferDetail::find($id);
    }

    /**
     * Soft delete an offer detail
     */
    protected function softDeleteOfferDetail(OfferDetail $offerDetail): void
    {
        $offerDetail->update([
            'deleted_by' => Auth::guard('api')->id() ?? 1
        ]);
        $offerDetail->delete();
    }
}
