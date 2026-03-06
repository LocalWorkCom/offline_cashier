<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\LeaveNational;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LeaveNationalController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $leave_nationals = LeaveNational::with(['countries', 'leaveTypes'])->get();
            return ResponseWithSuccessData($lang, $leave_nationals, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function add(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $validateData = Validator::make($request->all(), [
                'leave_type_id' => 'required|exists:leave_types,id',
                'country_id' => 'required|exists:countries,id',
                'name_ar' => 'required',
                'name_en' => 'required',
                'date' => 'required|date'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            $user_id = Auth::guard('api')->user()->id;
            $leave_national = new LeaveNational();
            $leave_national->leave_type_id = $request->leave_type_id;
            $leave_national->country_id = $request->country_id;
            $leave_national->name_ar = $request->name_ar;
            $leave_national->name_en = $request->name_en;
            $leave_national->date = $request->date;
            $leave_national->created_by = $user_id;
            $leave_national->save();

            return ResponseWithSuccessData($lang, $leave_national, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function edit(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $validateData = Validator::make($request->all(), [
                'id' => 'required|exists:leave_nationals,id',
                'leave_type_id' => 'required|exists:leave_types,id',
                'country_id' => 'required|exists:countries,id',
                'name_ar' => 'required',
                'name_en' => 'required',
                'date' => 'required|date'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            $user_id = Auth::guard('api')->user()->id;
            $leave_national = LeaveNational::findOrFail($request->id);
            $leave_national->leave_type_id = $request->leave_type_id;
            $leave_national->country_id = $request->country_id;
            $leave_national->name_ar = $request->name_ar;
            $leave_national->name_en = $request->name_en;
            $leave_national->date = $request->date;
            $leave_national->modified_by = $user_id;
            $leave_national->save();

            return ResponseWithSuccessData($lang, $leave_national, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $user_id = Auth::guard('api')->user()->id;

            $leave_national = LeaveNational::find($request->id);
            if (!$leave_national) {
                return  RespondWithBadRequestNotExist();
            }

            $leave_national->deleted_by = $user_id;
            $leave_national->save();

            $leave_national->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
