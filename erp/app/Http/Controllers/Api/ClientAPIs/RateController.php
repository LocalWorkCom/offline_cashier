<?php
namespace App\Http\Controllers\Api\ClientAPIs;

use App\Http\Controllers\Controller;
use App\Http\Requests\RateFormRequest;
use App\Http\Resources\RateResource;
use App\Models\Rate;
use App\Services\ClientServices\RateOldService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class RateController extends Controller
{
    private string $lang;
    private RateOldService $rateService;

    public function __construct(Request $request, RateOldService $rateService)
    {
        $this->lang = $request->header('lang', 'ar');
        $this->rateService = $rateService;

        if (!CheckToken()) {
            return RespondWithBadRequest($this->lang, 5);
        }
    }

    public function index(): JsonResponse
    {
        $rates = Rate::active()->get();
        return $rates->isEmpty()
            ? RespondWithBadRequest($this->lang, 2)
            : ResponseWithSuccessData($this->lang, RateResource::collection($rates), 1);
    }

    public function store(RateFormRequest $request): JsonResponse
    {
        $user = auth('api')->user();
        $lastOrder = $this->rateService->getLastCompletedOrder($user->id);

        if (!$lastOrder) {
            return RespondWithBadRequest($this->lang, 2);
        }

        if ($this->rateService->hasExistingRate($lastOrder->id)) {
            return respondErrorData(
                $this->lang === 'en' ? 'Rate already done.' : 'تم التقييم.',
                400,
                [$this->lang === 'en' ? 'The order has already been rated.' : 'تم تقييم الطلب مسبقاً.']
            );
        }

        $rate = $this->rateService->createRate([
            'client_id' => $user->id,
            'created_by' => $user->id,
            'order_id' => $lastOrder->id,
            'rate' => $request->value,
            'comment' => $request->note,
        ]);

        return ResponseWithSuccessData($this->lang, $rate, 1);
    }

    public function update(RateFormRequest $request, string $id): JsonResponse
    {
        $user = auth('api')->user();
        $rate = Rate::find($id);

        // Validate rate exists and belongs to the user
        if (!$rate) {
            return RespondWithBadRequest($this->lang, 2);
        }

        if ($rate->client_id != $user->id) {
            return RespondWithUnauthorizedRequest($this->lang, 4);
        }

        // Update the rate
        $updated = $this->rateService->updateRate($rate, [
            'rate' => $request->value,
            'comment' => $request->note,
        ]);

        return $updated
            ? ResponseWithSuccessData($this->lang, RateResource::make($rate->fresh()), 1)
            : RespondWithBadRequest($this->lang, 3); // Failed to update
    }

    public function show(string $id): JsonResponse
    {
        $rate = Rate::active()->find($id);
        return $rate
            ? ResponseWithSuccessData($this->lang, RateResource::make($rate), 1)
            : RespondWithBadRequest($this->lang, 2);
    }

    public function destroy(string $id): JsonResponse
    {
        $rate = Rate::find($id);

        if (!$rate) {
            return RespondWithBadRequest($this->lang, 2);
        }

        $rate->update(['deleted_by' => Auth::guard('api')->id() ?? 1]);
        $rate->delete();

        return ResponseWithSuccessData($this->lang, RateResource::make($rate), 1);
    }

    public function restore(string $id): JsonResponse
    {
        $rate = Rate::withTrashed()->find($id);
        return $rate
            ? tap($rate)->restore() && ResponseWithSuccessData($this->lang, RateResource::make($rate), 1)
            : RespondWithBadRequest($this->lang, 2);
    }
}
