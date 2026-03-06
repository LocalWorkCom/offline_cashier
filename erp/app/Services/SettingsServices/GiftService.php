<?php


namespace App\Services\SettingsServices;

use App\Models\Gift;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GiftService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function index(Request $request)
    {
        try {
            $gift = Gift::all();
            return ResponseWithSuccessData($this->lang, $gift, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function store(Request $request)
    {
        try {

            $request->validate([
                'name' => 'required|string',
                'expiration_date' => 'required|date|after:created_at'
            ]);

            $gift = Gift::create($request->all());

            return ResponseWithSuccessData($this->lang, $gift, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $gift = Gift::find($id);
            if (!$gift) {
                return RespondWithBadRequestData($this->lang, 22);
            }

            return ResponseWithSuccessData($this->lang, $gift, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $validatedData = $request->validate([
                'name' => 'required|string',
                'expiration_date' => 'required|date',
            ]);

            $gift = Gift::find($id);
            if (!$gift) {
                return RespondWithBadRequestData($this->lang, 22);
            }

            // Check if expiration date is after the creation date
            if (strtotime($validatedData['expiration_date']) <= strtotime($gift->created_at)) {
                return RespondWithBadRequestData($this->lang, 31);
            }

            $gift->update([
                'name' => $validatedData['name'],
                'expiration_date' => $validatedData['expiration_date']
            ]);

            return ResponseWithSuccessData($this->lang, $gift, 1);
        } catch (\Exception $e) {

            return RespondWithBadRequestData($this->lang, 2);
        }
    }


    public function destroy(Request $request, $id)
    {
        try {
            $gift = Gift::find($id);
            if (!$gift) {
                return RespondWithBadRequestData($this->lang, 22);
            }
            $gift->delete();
            return RespondWithSuccessRequest($this->lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function applyGiftToUsers(Request $request)
    {
        $validated = $request->validate([
            'gift_id' => 'required|exists:gifts,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $gift = Gift::find($validated['gift_id']);
        if (!$gift) {
            return RespondWithBadRequestData($this->lang, 8);
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

        return RespondWithSuccessRequest($this->lang,  32);
    }

    public function applyGiftByBranch(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'gift_id' => 'required|exists:gifts,id'
        ]);

        $gift = Gift::find($request->gift_id);

        if (!$gift) {
            return RespondWithBadRequestData($this->lang, 8);
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

        return RespondWithSuccessRequest($this->lang, 33);
    }
}
