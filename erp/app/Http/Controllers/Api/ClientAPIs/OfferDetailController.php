<?php

namespace App\Http\Controllers\Api\ClientAPIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfferDetailResource;
use App\Models\Dish;
use App\Models\DishAddon;
use App\Models\OfferDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class OfferDetailController extends Controller
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
        $details = OfferDetail::with('offer')->get();
        return ResponseWithSuccessData($this->lang, OfferDetailResource::collection($details), 1);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function save(Request $request, string $id = null)
    {
        $data = $request->validate([
            'offer_id' => 'required|numeric|exists:offers,id',
            'offer_type' => 'required|string|in:dishes',
            'type_id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($request) {
                    $offerType = $request->input('offer_type');
                    switch ($offerType) {
                        case 'products':
                            if (!Product::where('id', $value)->exists()) {
                                $this->lang == 'ar'? $fail('كود المنتج غير موجود')
                                : $fail('The selected type_id is invalid for products.');
                            }
                            break;

                        case 'dishes':
                            if (!Dish::where('id', $value)->exists()) {
                                $this->lang == 'ar'? $fail('كود الطبق غير موجود')
                                    : $fail('The selected type_id is invalid for dishes.');
                            }
                            break;

                        case 'addons':
                            if (!DishAddon::where('addon_id', $value)->exists()) {
                                $this->lang == 'ar'? $fail('كود الإضافة غير موجود')
                                    : $fail('The selected type_id is invalid for addons.');
                            }
                            break;

                        default:
                            $fail('Invalid offer type provided.');
                    }
                },
            ],
            'count' => 'required|numeric',
        ]);
        $id == null ?$data['created_by'] =Auth::guard('api')->user()->id??1
            : $data['modified_by'] =Auth::guard('api')->user()->id??1;

        $offer = OfferDetail::updateOrCreate(['id' => $id], $data);

        return ResponseWithSuccessData($this->lang, OfferDetailResource::make($offer), 1);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = OfferDetail::find($id);

        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        return ResponseWithSuccessData($this->lang, OfferDetailResource::make($data), 1);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = OfferDetail::find($id);

        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        $data->deleted_by = Auth::guard('api')->user()->id ?? 1;
        $data->save();
        $data->delete();

        return ResponseWithSuccessData($this->lang, OfferDetailResource::make($data), 1);
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        $data = OfferDetail::withTrashed()->find($id);

        if (!$data) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        $data->restore();
        return ResponseWithSuccessData($this->lang, OfferDetailResource::make($data), 1);
    }
}
