<?php

namespace App\Services\ClientServices;

use App\Http\Resources\RateResource;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\Rate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class RateOldService
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lang = app()->getLocale();
        $rates = Rate::get();
        return ResponseWithSuccessData($lang, RateResource::collection($rates), 1);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function save(Request $request, string $id = null)
    {
        $lang = app()->getLocale();
        $data = $request->validate([
            'value' => 'required|numeric|in:1,2,3,4,5',
            'note' => 'nullable|string',
            'active' => 'required|in:0,1',
        ]);
        $id == null ?$data['created_by'] =Auth::guard('api')->user()->id ?? 1
            : $data['modified_by'] =Auth::guard('api')->user()->id ?? 1;

        $rate = Rate::updateOrCreate(['id' => $id], $data);

        return ResponseWithSuccessData($lang, RateResource::make($rate), 1);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $lang = app()->getLocale();
        $data = Rate::find($id);

        if (!$data) {
            return RespondWithBadRequestData($lang, 2);
        }
        return ResponseWithSuccessData($lang, RateResource::make($data), 1);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lang = app()->getLocale();
        $data = Rate::find($id);

        if (!$data) {
            return RespondWithBadRequestData($lang, 2);
        }
        $data->deleted_by = Auth::guard('api')->user()->id ?? 1;
        $data->save();
        $data->delete();

        return ResponseWithSuccessData($lang, RateResource::make($data), 1);
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        $lang = app()->getLocale();
        $data = Rate::withTrashed()->find($id);

        if (!$data) {
            return RespondWithBadRequestData($lang, 2);
        }
        $data->restore();
        return ResponseWithSuccessData($lang, RateResource::make($data), 1);
    }

    public function getLastCompletedOrder(int $clientId): ?Order
    {
        return Order::where('client_id', $clientId)
            ->where('status', 'completed')
            ->latest()
            ->first();
    }

    public function hasExistingRate(int $orderId): bool
    {
        return Complaint::where('order_id', $orderId)->exists();
    }

    public function createRate(array $data): Complaint
    {
        return Complaint::create($data);
    }

    public function updateRate(Rate $rate, array $data): bool
    {
        return $rate->update($data);
    }
}
