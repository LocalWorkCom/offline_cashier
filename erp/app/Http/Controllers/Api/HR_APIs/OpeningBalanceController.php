<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\OpeningBalance;
use App\Models\ProductTransaction;
use App\Models\StoreTransaction;
use App\Models\StoreTransactionDetails;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use App\Events\ProductTransactionEvent;
use Auth;

class OpeningBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            if (!CheckToken()) {
                return RespondWithBadRequest($lang, 5);
            }
            $balance = OpeningBalance::where('deleted_at', null)->get();

            return ResponseWithSuccessData($lang, $balance, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            if (!CheckToken()) {
                return RespondWithBadRequest($lang, 5);
            }
            App::setLocale($lang);

            $validator = Validator::make($request->all(), [
                'amount' => 'required|integer',
                'price' => 'required|integer',
                'date' => 'required|date_format:Y-m-d',
                'product' => 'required|exists:products,id',
                'store' => 'required|exists:stores,id',
            ]);

            if ($validator->fails()) {
                return RespondWithBadRequestWithData($validator->errors());
            }

            // Check if the combination of product, store, and date already exists
            $exists = OpeningBalance::where('product_id', $request->product)
                                    ->where('store_id', $request->store)
                                    ->where('date', $request->date)
                                    ->exists();

            if ($exists) {
                return RespondWithBadRequestWithData([
                    'message' => __('This product already exists for the same store on the specified date.')
                ]);
            }

            // Create new OpeningBalance
            $balances = new OpeningBalance();
            $balances->product_id = $request->product;
            $balances->store_id = $request->store;
            $balances->amount = $request->amount;
            $balances->price = $request->price;
            $balances->date = $request->date;
            $balances->expired_date = $request->expired_date;
            $balances->created_by = auth()->id();
            $balances->save();

            //add product transaction evevt
            $total_price = 0;
            $type = 2;
            $to_type = 1;
            $store_id = $request->store;
            $user_id = Auth::guard('api')->user()->id;

            $add_store_bill = new StoreTransaction();
            $add_store_bill->type = $type;
            $add_store_bill->to_type = $to_type;
            $add_store_bill->to_id = $request->store;
            $add_store_bill->date = date('Y-m-d');
            $add_store_bill->total = 1;
            $add_store_bill->store_id = $store_id;
            $add_store_bill->user_id = 1;
            $add_store_bill->created_by = $user_id;
            $add_store_bill->total_price = $total_price;
            $add_store_bill->save();

            $store_transaction_id = $add_store_bill->id;
            $product_details = Product::findOrFail($request->product);

            $add_store_items = new StoreTransactionDetails();
            $add_store_items->store_transaction_id = $store_transaction_id;
            $add_store_items->product_id = $request->product;
            $add_store_items->product_unit_id = $product_details->main_unit_id;
            $add_store_items->country_id = $product_details->currency_code;
            $add_store_items->count = $request->amount;
            $add_store_items->price = $request->price;
            $add_store_items->total_price = ($request->amount * $request->price);
            $add_store_items->save();
            $add_store_items->type = $type;
            $add_store_items->to_type = $to_type;
            $add_store_items->user_id = 1;
            $add_store_items->store_id = $store_id;
            $add_store_items->expired_date = $request->expired_date;

            event(new ProductTransactionEvent($add_store_items));
            //end of add product transaction evevt

            return ResponseWithSuccessData($lang, $balances, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            if (!CheckToken()) {
                return RespondWithBadRequest($lang, 5);
            }
            App::setLocale($lang);

            $validator = Validator::make($request->all(), [
                "product_id" => "required|exists:products,id",
                "store" => "required|exists:stores,id",
                'amount' => 'required|integer',
                "price" => "required|integer",
                "date" => "required||date_format:Y-m-d",
            ]);

            if ($validator->fails()) {
                return RespondWithBadRequestWithData($validator->errors());
            }
            // Find the opening balance
            $balances = OpeningBalance::findOrFail($request->input('id'));

            // Check if there are any transactions for the product in this store, on or after the creation date
            $existingTransactions = ProductTransaction::where('product_id', $balances->product_id)
                ->where('store_id', $balances->store_id)
                ->whereDate('created_at', '>=', $balances->date)
                ->exists(); // Check if such transactions exist

            if ($existingTransactions) {
                return RespondWithBadRequest($lang, 7);
                // return response()->json([
                //     "status" => false,
                //     "message" => $lang == 'ar'
                //         ? "لا يمكنك تعديل الرصيد الافتتاحي لأن هناك معاملات على المنتج في هذا المتجر بعد هذا التاريخ."
                //         : "You cannot update the opening balance because there are transactions on this product in this store after the creation date."
                // ], 400);
            }
            $balances = OpeningBalance::findOrFail($request->input('id'));
            $balances->product_id  = $request->product_id;
            $balances->store_id  = $request->store;
            $balances->amount = $request->amount;
            $balances->price = $request->price;
            $balances->date = $request->date;
            $balances->expired_date = $request->expired_date;
            $balances->modified_by = auth()->id();
            $balances->save();

            return ResponseWithSuccessData($lang, $balances, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
