<?php

namespace App\Services\ClientServices;

use App\Models\Tip;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\MenusIntegration;

class TipService
{
    /**
     * Create a new tip.
     */
    public function create(array $data)
    {
        // dd($data);
        // return $data;
        $checkToken = true;
        if ($checkToken) {
            // Determine authenticated client ID
            $client_id = match (getAuthenticatedGuard()) {
                'api' => Auth::guard('api')->id(),
                'client' => Auth::guard('client')->id(),
                'employee' => Auth::guard('employee')->id(),
                default => null,
            };
            if (getAuthenticatedGuard() == 'employee') {
                $client_id = User::where('flag', 'unknown')->value('id');
            }
        } else {
            $client_id = Auth::guard('client')->user()->id;
        }

        $created_by = $client_id;
        $tip = new Tip();
        $tip->invoice_id = $data['response']['data']['invoice_id'] ?? null;
        $tip->order_id = $data['response']['data']['order_id'] ?? null;
        $tip->payment_method = $data['request']['payment_method'] ?? 'cash';
        $tip->payment_amount = $data['request']['payment_amount'] ?? 0;
        $tip->change_amount = $data['request']['change_amount'] ?? 0;
        $tip->tips_aption = $data['request']['menu_integration'] == false ? $data['request']['tips_aption'] : 'no_tip';
        $tip->tip_amount = $data['request']['tip_amount'] ??    0;
        $tip->bill_amount = $data['request']['bill_amount'] ?? 0;
        $tip->tip_specific_amount = $data['request']['tip_specific_amount'] ?? 0;
        $tip->returned_amount = $data['request']['returned_amount'] ?? 0;
        $tip->total_with_tip = $data['request']['total_with_tip'] ?? 0;
        $tip->date = now()->toDateString();
        $tip->created_by = $created_by;
        // if(isset($data['request']['menu_integration']) && $data['request']['menu_integration'] == true){

        //     $menu = MenusIntegration::where('name_en', 'LIKE', '%' . $data['request']['type'] . '%')->first();
        // }
        $menu = (isset($data['request']['menu_integration']) && $data['request']['menu_integration'] == true)
            ? MenusIntegration::where('name_en', 'LIKE', '%' . $data['request']['type'] . '%')->first()
            : null;

        $tip->menus_integration_id = $menu?->id;

        // $tip->menus_integration_id = $menu ?  $menu->id : null;
        $tip->payment_status_menu_integration = $data['request']['payment_status_menu_integration'] ?? null;
        $tip->payment_method_menu_integration = $data['request']['payment_method_menu_integration'] ?? null;
        $tip->save();

        return $tip;

        // return Tip::create($data);
    }

    /**
     * Update a tip.
     */
    // public function update(Tip $tip, array $data): Tip
    // {
    //     $tip->update($data);
    //     return $tip;
    // }
    public function update(array $data): Tip
    {
        $checkToken = true;

        if ($checkToken) {
            // Determine authenticated client ID
            $client_id = match (getAuthenticatedGuard()) {
                'api' => Auth::guard('api')->id(),
                'client' => Auth::guard('client')->id(),
                'employee' => Auth::guard('employee')->id(),
                default => null,
            };

            if (getAuthenticatedGuard() == 'employee') {
                $client_id = User::where('flag', 'unknown')->value('id');
            }
        } else {
            $client_id = Auth::guard('client')->user()->id;
        }

        $created_by = $client_id;

        // ✅ Use updateOrCreate
        $tip = Tip::updateOrCreate(
            [
                // Condition to check if tip already exists
                'invoice_id' => $data['invoice_id'],
                'order_id' => $data['order_id'],
            ],
            [
                'payment_method' => $data['request']['tip']['payment_method'] ?? 'cash',
                'payment_amount' => $data['request']['tip']['payment_amount'] ?? 0,
                'change_amount' => $data['request']['tip']['change_amount'] ?? 0,
                'tips_aption' => $data['request']['tip']['tips_aption'] ?? 'no_tip',
                'tip_amount' => $data['request']['tip']['tip_amount'] ?? 0,
                'bill_amount' => $data['request']['tip']['bill_amount'] ?? 0,
                'tip_specific_amount' => $data['request']['tip']['tip_specific_amount'] ?? 0,
                'returned_amount' => $data['request']['tip']['returned_amount'] ?? 0,
                'total_with_tip' => $data['request']['tip']['total_with_tip'] ?? 0,

                'date' => now()->toDateString(),
                'created_by' => $created_by,
            ]
        );

        return $tip;
    }

    /**
     * Delete a tip.
     */
    public function delete(Tip $tip): bool
    {
        return $tip->delete();
    }

    /**
     * Get tips by invoice id.
     */
    public function getByInvoice(int $invoiceId)
    {
        return Tip::where('invoice_id', $invoiceId)->get();
    }

    /**
     * Get a single tip.
     */
    public function find(int $id): ?Tip
    {
        return Tip::find($id);
    }
}
