<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\ShiftDetail;
use App\Services\HR_Services\ShiftService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ShiftController extends Controller
{
    protected $shiftService;

    public function __construct(ShiftService $shiftService)
    {
        $this->shiftService = $shiftService;
    }

    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            App::setLocale($lang);

            $shifts = $this->shiftService->index();
            $response = paginateOrGetAll($shifts, $request, ['']);
            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching shifts: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            App::setLocale($lang);

            $shift = $this->shiftService->show($id, $lang);
            if ($shift['status'] === 'error') {
                return respondError($shift['message'], 404);
            }
            return ResponseWithSuccessData($lang, $shift, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching shift: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function store(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            App::setLocale($lang);

            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string|max:255',
                'name_en' => 'nullable|string|max:255',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'details' => 'required|array',
                'details.*.day_index' => 'required|integer|min:0|max:6',
                'details.*.timetable_id' => 'required|exists:timetables,id',
            ]);

            if ($validator->fails()) {
                return respondError($validator->errors(), 400);
            }

            $shift = $this->shiftService->store($request);

            return ResponseWithSuccessData($lang, $shift, 1);
        } catch (\Exception $e) {
            Log::error('Error creating shift: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            App::setLocale($lang);

            $shift = Shift::find($id);
            if (!$shift) {
                $message = $lang == 'en' ? 'Shift not found' : 'الوردية غير موجودة';
                return respondError($message, 404);
            }

            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string|max:255',
                'name_en' => 'nullable|string|max:255',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'details' => 'required|array',
                'details.*.day_index' => 'required|integer|min:0|max:6',
                'details.*.timetable_id' => 'required|exists:timetables,id',
            ]);

            if ($validator->fails()) {
                return respondError($validator->errors(), 400);
            }

            $shift = $this->shiftService->update($request, $id);

            return ResponseWithSuccessData($lang, $shift, 1);
        } catch (\Exception $e) {
            Log::error('Error updating shift: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function destroy(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            App::setLocale($lang);

            $shift = Shift::find($id);
            if (!$shift) {
                $message = $lang == 'en' ? 'Shift not found' : 'الوردية غير موجودة';
                return respondError($message, 404);
            }
            $shift = $this->shiftService->delete($id, $lang);
            if ($shift['status'] === 'error') {
                return respondError($shift['message'], $shift['code']);
            }
            return ResponseWithSuccessData($lang, null, 1);
        } catch (\Exception $e) {
            Log::error('Error deleting shift: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function restore(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            App::setLocale($lang);

            $shift = Shift::onlyTrashed()->findOrFail($id);
            $shift->restore();

            return ResponseWithSuccessData($lang, $shift->load('details.timetable'), 1);
        } catch (\Exception $e) {
            Log::error('Error restoring shift: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
