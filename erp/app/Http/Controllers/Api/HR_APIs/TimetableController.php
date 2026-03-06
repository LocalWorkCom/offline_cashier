<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Timetable;
use App\Services\HR_Services\TimetableService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TimetableController extends Controller
{
    protected $timetableService;

    public function __construct(TimetableService $timetableService)
    {
        $this->timetableService = $timetableService;
    }

    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $timetables = $this->timetableService->index();
            $response = paginateOrGetAll($timetables, $request, ['']);
            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching timetables: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            $timetable = $this->timetableService->show($id);
            if (!$timetable) {
                $message = $lang == 'en' ? 'Timetable not found' : 'الجدول الزمني غير موجود';
                return respondError($message, 404);
            }
            return ResponseWithSuccessData($lang, $timetable, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching timetable: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function store(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');

            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'on_duty_time' => 'required|date_format:H:i',
                'off_duty_time' => 'required|date_format:H:i|after:on_duty_time',
                'start_sign_in' => 'required|date_format:H:i',
                'end_sign_in' => 'required|date_format:H:i|after:start_sign_in',
                'start_sign_out' => 'required|date_format:H:i',
                'end_sign_out' => 'required|date_format:H:i|after:start_sign_out',
                'lateness_grace_period' => 'required|integer|min:0|max:1440',
                'start_late_time_option' => 'required|in:after_duty_time_grace_period,after_duty_time,from_duty_time',
                'cross_day' => 'required|boolean',
            ], [
                'off_duty_time.after' => __('validation.offDutyAfterOnDuty'),
                'end_sign_in.after' => __('validation.endSignInAfterStart'),
                'end_sign_out.after' => __('validation.endSignOutAfterStart'),
            ]);
            if ($validator->fails()) {
                return respondError('Validation Error.', 400, $validator->errors());
            }

            $timetable = $this->timetableService->store($request);
            return ResponseWithSuccessData($lang, $timetable, 1);
        } catch (\Exception $e) {
            Log::error('Error creating timetable: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');

            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'on_duty_time' => 'required|date_format:H:i',
                'off_duty_time' => 'required|date_format:H:i|after:on_duty_time',
                'start_sign_in' => 'required|date_format:H:i',
                'end_sign_in' => 'required|date_format:H:i|after:start_sign_in',
                'start_sign_out' => 'required|date_format:H:i',
                'end_sign_out' => 'required|date_format:H:i|after:start_sign_out',
                'lateness_grace_period' => 'required|integer|min:0|max:1440',
                'start_late_time_option' => 'required|in:after_duty_time_grace_period,after_duty_time,from_duty_time',
                'cross_day' => 'required|boolean',
            ], [
                'off_duty_time.after' => __('validation.offDutyAfterOnDuty'),
                'end_sign_in.after' => __('validation.endSignInAfterStart'),
                'end_sign_out.after' => __('validation.endSignOutAfterStart'),
            ]);

            if ($validator->fails()) {
                return respondError('Validation Error.', 400, $validator->errors());
            }

            $timetable = $this->timetableService->update($request, $id, $lang);
            if ($timetable['status'] === 'error') {
                return respondError($timetable['message'], 404);
            }
            return ResponseWithSuccessData($lang, $timetable, 1);
        } catch (\Exception $e) {
            Log::error('Error updating timetable: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function destroy(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');

            $timetable = $this->timetableService->delete($id, $lang);
            if ($timetable['status'] === 'error') {
                $message = $lang == 'en' ? 'Timetable not found' : 'الجدول الزمني غير موجود';
                return respondError($timetable['message'], $timetable['code']);
            }
            return RespondWithSuccessMsg($timetable['message']);
        } catch (\Exception $e) {
            Log::error('Error deleting timetable: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function restore(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            $timetable = Timetable::onlyTrashed()->findOrFail($id);

            $timetable->restore();


            return ResponseWithSuccessData($lang, $timetable, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring timetable: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
