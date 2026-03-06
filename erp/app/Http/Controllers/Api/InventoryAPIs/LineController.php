<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Line;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LineController extends Controller
{
    /**
     * Display a listing of the lines.
     */
    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $withTrashed = $request->query('withTrashed', false); 
    
            $lines = $withTrashed
                ? Line::withTrashed()->with(['store', 'creator', 'deleter'])->get()
                : Line::with(['store', 'creator', 'deleter'])->get();
    
            // Return a successful response with the data (even if it's empty)
            return ResponseWithSuccessData($lang, $lines, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching lines: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2); 
        }
    }

    /**
     * Store a newly created line in storage.
     */
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        // Validate the request
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer|exists:stores,id',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'is_freeze' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        try {
            $line = Line::create([
                'store_id' => $request->store_id,
                'name_en' => $request->name_en,
                'name_ar' => $request->name_ar,
                'is_freeze' => $request->is_freeze ?? 1,
                'created_by' => auth()->id(),
            ]);

            return ResponseWithSuccessData($lang, $line, 1);
        } catch (\Exception $e) {
            Log::error('Error creating line: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Display the specified line.
     */
    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $line = Line::withTrashed()->with(['store', 'creator', 'deleter'])->findOrFail($id);
            return ResponseWithSuccessData($lang, $line, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Update the specified line in storage.
     */
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        // Validate the request
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|integer|exists:stores,id',
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'required|string|max:255',
            'is_freeze' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        try {
            $line = Line::findOrFail($id);

            $line->update([
                'store_id' => $request->store_id,
                'name_en' => $request->name_en,
                'name_ar' => $request->name_ar,
                'is_freeze' => $request->is_freeze ?? $line->is_freeze,
                'modified_by' => auth()->id(),
            ]);

            return ResponseWithSuccessData($lang, $line, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Soft delete the specified line from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $line = Line::findOrFail($id);
            $line->update(['deleted_by' => auth()->id()]);
            $line->delete();

            return ResponseWithSuccessData($lang, null, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Restore a soft-deleted line.
     */
    public function restore(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $line = Line::withTrashed()->findOrFail($id);
            $line->restore();

            return ResponseWithSuccessData($lang, $line, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
