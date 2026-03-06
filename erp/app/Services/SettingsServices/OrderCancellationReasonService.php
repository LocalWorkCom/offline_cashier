<?php

namespace App\Services\SettingsServices;

use App\Models\OrderCancellationReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderCancellationReasonService
{
    private $types = ["waiter", "driver", "client", "branch manager"];

    public function index($withTrashed = false)
    {
        try {
            $lang = app()->getLocale();
             return $withTrashed
            ? OrderCancellationReason::withTrashed()
            : OrderCancellationReason::query()
            ->whereNull('deleted_at');
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }
    private function validateData($data)
    {
        $validator = Validator::make($data, [
            'reason_ar' => 'required|string|max:255',
            'reason_en' => 'required|string|max:255',
            'type' => 'required|array|min:1',
            'type.*' => 'in:waiter,driver,client,branch manager',
        ]);

        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }

        return $validator->validated(); // Returns valid array
    }
    public function store($request)
    {
        try {
            $lang = app()->getLocale();

            $validatedData = $this->validateData($request);

            // If validateData returns a JsonResponse (custom error), return it directly
            if ($validatedData instanceof \Illuminate\Http\JsonResponse) {
                return $validatedData;
            }
            $validatedData['created_by'] = authActionSave()['by'];
            $validatedData['created_by_type'] = authActionSave()['type'];
            // $data = $request->only('reason_ar', 'reason_en', 'type');
            // $data['type'] = $request->type ?: []; // Ensure array even if empty
            $reason = OrderCancellationReason::create($validatedData);
            return ResponseWithSuccessData($lang, $reason, 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    public function update($data,$id)
    {
        // try {
            $reason = OrderCancellationReason::findOrFail($id);

            $validatedData = $this->validateData($data);
            if ($validatedData instanceof \Illuminate\Http\JsonResponse) {
                return $validatedData;
            }
            $validatedData['updated_by'] = authActionSave()['by'];
            $validatedData['updated_by_type'] = authActionSave()['type'];
            $reason->update($validatedData);
            return $reason;
        // } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        //     return  RespondWithBadRequestNotExist();
        // }
    }

    public function destroy(string $id)
    {
        try {
            $lang = app()->getLocale();
            $reason = OrderCancellationReason::findOrFail($id);
            $reason->delete();
            return ResponseWithSuccessData($lang, $reason, 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    public function show($id)
    {
        return OrderCancellationReason::findOrFail($id);
    }
     public function delete($id)
    {
        try {
            // Find the addon category or fail
            $reason = OrderCancellationReason::findOrFail($id);

            // Track the deleter from either guard
            $reason->update([
                'deleted_by' => authActionSave()['by'],
                'deleted_by_type' => authActionSave()['type']
            ]);

            // Perform soft delete
            $reason->delete();

            // Success response
            return true;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return  RespondWithBadRequestNotExist();
        } catch (\Exception $e) {
            Log::error('Error deleting addon category: ' . $e->getMessage());
            return respondError(__('Something went wrong'), 500);
        }
    }
}
