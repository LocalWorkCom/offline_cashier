<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Division;
use Illuminate\Support\Facades\Validator;

class DivisionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $withTrashed = $request->query('withTrashed', false);

            $divisions = $withTrashed
                ? Division::withTrashed()->with(['line', 'creator', 'deleter'])->get()
                : Division::with(['line', 'creator', 'deleter'])->get();

            return ResponseWithSuccessData($lang, $divisions, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar'); 

        $validator = Validator::make($request->all(), [
            'line_id' => 'required|integer|exists:lines,id',
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        try {
            $division = Division::create([
                'line_id' => $request->line_id,
                'name_en' => $request->name_en,
                'name_ar' => $request->name_ar,
                'created_by' => auth()->id(),
            ]);

            return ResponseWithSuccessData($lang, $division, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        $validator = Validator::make($request->all(), [
            'line_id' => 'required|integer|exists:lines,id',
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        try {
            $division = Division::findOrFail($id);

            $division->update([
                'line_id' => $request->line_id,
                'name_en' => $request->name_en,
                'name_ar' => $request->name_ar,
                'modified_by' => auth()->id(),
            ]);

            return ResponseWithSuccessData($lang, $division, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Soft delete the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $division = Division::findOrFail($id);
            $division->update(['deleted_by' => auth()->id()]);
            $division->delete();

            return ResponseWithSuccessData($lang, null, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Restore a soft-deleted division.
     */
    public function restore(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $division = Division::withTrashed()->findOrFail($id);
            $division->restore();

            return ResponseWithSuccessData($lang, $division, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
