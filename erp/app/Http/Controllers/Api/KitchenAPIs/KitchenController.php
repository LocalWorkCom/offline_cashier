<?php

namespace App\Http\Controllers\Api\KitchenAPIs;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\DishDetail;
use App\Models\DishIngredientStep;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class KitchenController extends Controller
{
    public function orderDishDetails(Request $request)
    {
        $orderId = $request->orderId;
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $employee = auth('employee')->user();

        if (!$employee) {
            return RespondWithBadRequest($lang, 4);
        }

        $orders = Order::with(['branch', 'tracking', 'orderDetails.dish', 'orderDetails.dishAddons'])->where('id', $orderId)
            // where('branch_id', $employee->branch_id)
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.',
                'errorData' => ['error' => $lang == 'en' ? 'Order does not exist' : 'الطلب غير موجود.'],
                'data' => null
            ], 200);
        }

        $responseData = $orders->map(function ($order) use ($lang) {

            $orderDetails = $order->orderDetails->map(function ($detail) use ($lang) {
                $orderDetailTotal = $detail->price_befor_tax;
                $addonsTotal = $detail->dishAddons->sum('price_before_tax');
                // $total = $orderDetailTotal + $addonsTotal;
                return [
                    // 'item_id' => $detail->id,
                    'dish_id' => $detail->dish_id,
                    'dish_name' => ($lang === 'ar') ? $detail->dish->name_ar ?? null : $detail->dish->name_en ?? null,
                    'size_id' => $detail->dish_size_id ?? null,
                    'size' => $detail->dish_size_id ?
                        (($lang === 'ar') ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
                        : null,
                    'quantity' => $detail->quantity,
                    'note' => $detail->note,
                    'image' => $detail->image,
                    'addons' => $detail->dishAddons->map(function ($addon) use ($lang) {
                        return [
                            'addon_category_id' => $addon->Addon->addon_category_id,
                            'addon_id' => $addon->Addon->addon_id,
                            'addon_name' => ($lang === 'ar') ? $addon->Addon->addons->name_ar ?? null : $addon->Addon->addons->name_en ?? null,
                        ];
                    }),
                ];
            });
            // $currencySymbol = $order->Branch?->country?->currency_symbol ?? 'ج.م';
            // $orderItemsCount = $order->orderDetails->sum('quantity');

            return [
                'order_id' => $order->id,
                'order_details' => $orderDetails,
                'order_notes' => $order->note,
            ];
        });
        $response = [
            'orderDetails' => $responseData,
        ];
        return ResponseWithSuccessData($lang, $response, 1);
    }
    public function getIngrediantSteps(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $user = auth('employee')->user();

        if ((!$user) || ($user->flag != 'Head Chef')) {
            return RespondWithBadRequest($lang, 4);
        }
        $dish_id = $request->dish_id;
        $size_id = $request->size_id ?? null;

        // Get recipe steps
        $recipe_data = DishIngredientStep::where('dish_id', $dish_id)
            ->get(['recipe_title', 'recipe_steps']);

        $recipe_steps = $recipe_data->map(function ($item) {
            $desc = json_decode($item->recipe_steps, true) ?? [];
            $title = trim($item->recipe_title);

            if (empty($title) || empty($desc)) {
                return null; // Skip empty values
            }

            return [
                'title' => $title,
                'desc' => $desc,
            ];
        })->filter()->values()->toArray();

        // Get note steps
        $note_data = DishIngredientStep::where('dish_id', $dish_id)
            ->get(['note_title', 'note_steps']);

        $note_steps = $note_data->map(function ($item) {
            $desc = json_decode($item->note_steps, true) ?? [];
            $title = trim($item->note_title);

            if (empty($title) || empty($desc)) {
                return null; // Skip empty values
            }

            return [
                'title' => $title,
                'desc' => $desc,
            ];
        })->filter()->values()->toArray();

        $dish_details = DishDetail::where('dish_id', $dish_id)
            ->where('dish_size_id', $size_id)
            ->get();
        $recipe = [];
        // dd($dish_details);
        foreach ($dish_details as $dish_detail) {
            if ($dish_detail->recipe) { // Ensure the recipe exists
                foreach ($dish_detail->recipe->ingredients as $value) {
                    $recipe[] = [
                        'name' => $value->product->name,
                        'quantity' => $value->quantity
                    ];
                }
            }
        }

        if ($dish_id != null)
        {
            $name = $lang == 'en' ? Dish::find($dish_id)->name_en : Dish::find($dish_id)->name_ar ;
            $image = Dish::find($dish_id)->image;
        }
        else
        {
            $image = null;
        }

        // Prepare response
        $response = [
            'Ingredient' => $recipe,
            'recipe_steps' => $recipe_steps,
            'note_steps' => $note_steps,
            'name' =>$name,
            'image' =>$image
        ];

        return ResponseWithSuccessData($lang, $response, 1);
    }
}
