<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Models\Gift;
use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GiftController extends Controller
{
    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $gift = Gift::all();
            return ResponseWithSuccessData($lang, $gift, 1);
        } catch (Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function store(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');

            $request->validate([
                'name' => 'required|string',
                'expiration_date' => 'required|date|after:created_at'
            ]);

            $gift = Gift::create($request->all());

            return ResponseWithSuccessData($lang, $gift, 1);
        } catch (Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $gift = Gift::find($id);
            if (!$gift) {
                return RespondWithBadRequestData($lang, 22);
            }

            return ResponseWithSuccessData($lang, $gift, 1);
        } catch (Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');

            $validatedData = $request->validate([
                'name' => 'required|string',
                'expiration_date' => 'required|date',
            ]);

            $gift = Gift::find($id);
            if (!$gift) {
                return RespondWithBadRequestData($lang, 22);
            }

            // Check if expiration date is after the creation date
            if (strtotime($validatedData['expiration_date']) <= strtotime($gift->created_at)) {
                return RespondWithBadRequestData($lang, 31);
            }

            $gift->update([
                'name' => $validatedData['name'],
                'expiration_date' => $validatedData['expiration_date']
            ]);

            return ResponseWithSuccessData($lang, $gift, 1);
        } catch (Exception $e) {

            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function destroy(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $gift = Gift::find($id);
            if (!$gift) {
                return RespondWithBadRequestData($lang, 22);
            }
            $gift->delete();
            return RespondWithSuccessRequest($lang, 1);
        } catch (Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function applyGiftToUsers(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $validated = $request->validate([
            'gift_id' => 'required|exists:gifts,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $gift = Gift::find($validated['gift_id']);
        if (!$gift) {
            return RespondWithBadRequestData($lang, 8);
        }

        // Apply the gift to the specified users
        foreach ($request->user_ids as $userId) {
            // Check if the gift is already applied to this user
            $userGiftExists = DB::table('user_gifts')
                ->where('user_id', $userId)
                ->where('gift_id', $request->gift_id)
                ->exists();

            if (!$userGiftExists) {
                DB::table('user_gifts')->insert([
                    'user_id' => $userId,
                    'gift_id' => $request->gift_id,
                    'used' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return RespondWithSuccessRequest($lang,  32);
    }

    public function applyGiftByBranch(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'gift_id' => 'required|exists:gifts,id'
        ]);

        $gift = Gift::find($request->gift_id);

        if (!$gift) {
            return RespondWithBadRequestData($lang, 8);
        }

        // Get all client IDs who have placed orders in the specified branch
        $orders = Order::where('branch_id', $request->branch_id)
            ->select('client_id')
            ->distinct()
            ->get();

        foreach ($orders as $order) {
            $userId = $order->client_id;

            // Check if the gift is already applied to this user
            $userGiftExists = DB::table('user_gifts')
                ->where('user_id', $userId)
                ->where('gift_id', $request->gift_id)
                ->exists();

            if (!$userGiftExists) {
                DB::table('user_gifts')->insert([
                    'user_id' => $userId,
                    'gift_id' => $request->gift_id,
                    'used' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        return RespondWithSuccessRequest($lang, 33);
    }
}
