<?php

namespace App\Traits;

use App\Models\OrderWasteLog;
use Illuminate\Support\Facades\Auth;

trait OrderWasteLogTrait
{
    protected function logWasteAction($waste, $action, $oldData = null, $changedFields = null, $notes = null)
    {
        $userId = Auth::guard('employee')->id();

        $logData = [
            'order_waste_id' => $waste->id,
            'created_by' => $userId,
            'action' => $action,
            'order_id' => $waste->order_id,
            'invoice_id' => $waste->invoice_id,
            'return_invoice_request_id' => $waste->return_invoice_request_id,
            'order_detail_id' => $waste->order_detail_id,
            'order_addon_id' => $waste->order_addon_id,
            'original_quantity' => $waste->original_quantity,
            'waste_quantity' => $waste->waste_quantity,
            'waste_reason_id' => $waste->waste_reason_id,
            'type' => $waste->type,
            'flag' => $waste->flag,
            'reused' => $waste->reused,
            'note' => $notes ?? $waste->note, // Use the passed notes or fall back to waste's note
            'changed_fields' => $changedFields ? implode(',', $changedFields) : null,
        ];

        OrderWasteLog::create($logData);
    }

    protected function getChangedFields($oldData, $newData)
    {
        $changedFields = [];

        foreach ($newData as $key => $value) {
            if (array_key_exists($key, $oldData)) {
                if ($oldData[$key] != $value) {
                    $changedFields[] = $key;
                }
            }
        }

        return $changedFields;
    }
}
