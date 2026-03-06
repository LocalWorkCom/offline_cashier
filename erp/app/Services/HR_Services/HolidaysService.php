<?php

namespace App\Services\HR_Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Country;
use App\Models\LeaveNational;
use App\Models\Employee;
use App\Models\LeaveRequest;

class HolidaysService
{
    public function getAllHolidays($countryCode, Carbon $date)
    {
        $useEnglish = "ar";
        $apiKey = env('GOOGLE_API_KEY');
        $year = $date->year;
        $start = Carbon::create($year, 1, 1)->toIso8601String();
        $end = Carbon::create($year, 12, 31)->toIso8601String();

        $holidays = [];

        // Step 1: Fetch in preferred language
        $calendarId = $this->getGoogleCalendarId($countryCode, $useEnglish);
        $holidays = $this->fetchHolidays($calendarId, $apiKey, $start, $end);

        // Step 3: Filter holidays (inside the function)
        $holidays = $this->filterOfficialHolidays($holidays);

        // Step 4: Format for FullCalendar
        $events = array_map(function ($holiday) {
            return [
                'name' => $holiday['summary'],
                'name_en' => $holiday['summary'],
                'date' => $holiday['start']['date']
            ];
        }, $holidays);

        // $items = $holidays->json('items') ?? [];
        // $all_holidays = [];

        // foreach ($items as $item) {
        //     $holidays[] = [
        //         'date' => $item['start']['date'],
        //         'name' => $useEnglish ? $item['summary'] : $item['summary'], // you can adjust translation here
        //         'name_en' => $item['summary'],
        //     ];
        // }

        return $events = array_values($events);
    }

    public function getHolidays($countryCode, $date, $useEnglish, $type)
    {
        // return $useEnglish;
        $apiKey = env('GOOGLE_API_KEY');
        $holidays = [];
        // if($type == "all"){
        //     $startOfYear = $date->startOfYear()->toIso8601String();
        // }else{
        //     $startOfYear = $date->toIso8601String();
        // }

        $startOfYear = $date->startOfYear()->toIso8601String();
        // $endOfYear = $date->endOfYear()->toIso8601String();

        if ($type == "partial") {
            $endOfYear = Carbon::now()->toIso8601String();
        } else {
            $endOfYear = $date->endOfYear()->toIso8601String();
        }

        try {
            // Step 1: Fetch in preferred language
            $calendarId = $this->getGoogleCalendarId($countryCode, $useEnglish);
            $holidays = $this->fetchHolidays($calendarId, $apiKey, $startOfYear, $endOfYear);

            // Step 2: If holidays are empty, retry with alternative language
            if (empty($holidays)) {
                $calendarId = $this->getGoogleCalendarId($countryCode, $useEnglish);
                $holidays = $this->fetchHolidays($calendarId, $apiKey, $startOfYear, $endOfYear);
            }

            // Step 3: Filter holidays (inside the function)
            $holidays = $this->filterOfficialHolidays($holidays);

            // Step 4: Format for FullCalendar
            $events = array_map(function ($holiday) {
                return [
                    'title' => $holiday['summary'],
                    'start' => $holiday['start']['date'],
                    'allDay' => true,
                ];
            }, $holidays);

            return $events = array_values($events);
        } catch (\Exception $e) {
            Log::error('Failed to fetch holidays: ' . $e->getMessage());
        }
    }

    private function fetchHolidays($calendarId, $apiKey, $timeMin, $timeMax)
    {
        try {
            $response = Http::get("https://www.googleapis.com/calendar/v3/calendars/$calendarId/events", [
                'key' => $apiKey,
                'timeMin' => $timeMin,
                'timeMax' => $timeMax,
            ]);

            if ($response->successful()) {
                return $response->json()['items'] ?? [];
            }
        } catch (\Exception $e) {
            Log::warning("Failed to fetch holidays for $calendarId: " . $e->getMessage());
        }

        return [];
    }

    private function filterOfficialHolidays(array $holidays)
    {
        $officialKeywords = [
            'عطلة عامة',     // Arabic
            'Public holiday' // English
        ];

        return array_filter($holidays, function ($holiday) use ($officialKeywords) {
            $desc = strtolower($holiday['description'] ?? '');
            foreach ($officialKeywords as $keyword) {
                if (str_contains($desc, strtolower($keyword))) {
                    return true;
                }
            }
            return false;
        });
    }

    private function getGoogleCalendarId($countryCode, $language = 'ar')
    {
        // Mapping of country codes to Google's naming convention
        $countryMap = [
            'us' => 'usa',
            'gb' => 'uk',
            'ca' => 'canadian',
            'de' => 'german',
            'fr' => 'french',
            'it' => 'italian',
            'jp' => 'japanese',
            'ind' => 'indian',
            'in' => 'indonesian',
            'kz' => 'kz',
            'ir' => 'ir',
            'iq' => 'iq',
            'saudiarabian' => 'saudiarabian',
            'malaysia' => 'malaysia',
            'Morocco' => 'ma',
            'Syria' => 'sy',
            'Turkey' => 'turkish',
            'Tunisia' => 'tn',
            'Qatar' => 'qa',
            'Sudan' => 'sd',
            'South Sudan' => 'ss',
            'Mozambique' => 'mz',
            'North Korea' => 'kp',
            'Portugal' => 'portuguese',
            'Russia' => 'russian',
            'South Africa' => 'sa',
            'South Korea' => 'south_korea',
            'South Spain' => 'spain',
            'South Tajikistan' => 'tj',
            'South Tanzania' => 'tz',
            'is' => 'islamic',
            'ch' => 'christian',
        ];

        // Get mapped name or default to country code
        $calendarCountry = $countryMap[$countryCode] ?? $countryCode;

        // Build calendar ID in Google format
        return "{$language}.{$calendarCountry}%23holiday@group.v.calendar.google.com";
    }

    public function index(Request $request)
    {
        $lang =  $request->lang;
        try {
            // Validate the input
            $validator = Validator::make($request->all(), [
                'country_id' => 'required|exists:countries,id',
                'year' => 'required|integer|min:1900|max:2100'
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $countryCode = Country::where('id', $request->country_id)->first()->code;
            $leave_nationals = LeaveNational::where('country_code', $countryCode)->whereYear('holiday_date', $request->year)->get();

            return ResponseWithSuccessData($lang, $leave_nationals, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $lang =  $request->lang;
            $validateData = Validator::make($request->all(), [
                'date' => 'required|date',
                'status' => 'required|in:0,1'
            ]);

            if ($validateData->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validateData->errors());
            }

            $employee = auth('employee')->user();
            if (isset($request->employee_id) && $employee->id != $request->employee_id) {
                $get_employee = Employee::where('id', $request->employee_id)->first();
                if ($get_employee) {
                    $employee_id = $get_employee->id;
                    $position_id = $get_employee->position_id;
                }
            } else {
                $employee_id = $employee->id;
                $position_id = $employee->position_id;
            }

            $leave_national = LeaveNational::find($id);
            if (!$leave_national) {
                return respondError(
                    $lang == 'en' ? 'Sorry, We did not found request' : 'عفوا لا يوجد طلب ',
                    400,
                    $lang == 'en' ? ['Sorry, We did not found request to make search for it'] : ['عفوا لا يوجد طلب ليتم البحث عليه']
                );
            }

            $leave_national->holiday_date = $request->date;
            $leave_national->status = $request->status;
            $leave_national->modified_by = $employee->id;
            $leave_national->save();

            if ($leave_national->holiday_date != $leave_national->current_date) {
                $existsRequests = LeaveRequest::where('leave_type_id', $leave_national->leave_type_id)->where('from', $leave_national->holiday_date)->get();
                if (count($existsRequests) > 0) {
                    foreach ($existsRequests as $existsRequest) {
                        if ($leave_national->status == 0) {
                            $existsRequest->deleted_by = $employee->id;
                            $existsRequest->save();
                            $existsRequest->delete();
                        } else {
                            $existsRequest->from = $request->date;
                            $existsRequest->modified_by = $employee->id;
                            $existsRequest->save();
                        }
                    }
                }
            }

            return ResponseWithSuccessData($lang, $leave_national, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function ChangeStatus(Request $request)
    {
        try {
            $lang =  $request->lang;
            $validateData = Validator::make($request->all(), [
                'id' => 'required|date',
                'status' => 'required|date'
            ]);

            if ($validateData->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validateData->errors());
            }

            $employee = auth('employee')->user();
            if (isset($request->employee_id) && $employee->id != $request->employee_id) {
                $get_employee = Employee::where('id', $request->employee_id)->first();
                if ($get_employee) {
                    $employee_id = $get_employee->id;
                    $position_id = $get_employee->position_id;
                }
            } else {
                $employee_id = $employee->id;
                $position_id = $employee->position_id;
            }

            $leave_national = LeaveNational::find($id);
            if (!$leave_national) {
                return respondError(
                    $lang == 'en' ? 'Sorry, We did not found request' : 'عفوا لا يوجد طلب ',
                    400,
                    $lang == 'en' ? ['Sorry, We did not found request to make search for it'] : ['عفوا لا يوجد طلب ليتم البحث عليه']
                );
            }

            $leave_national->holiday_date = $request->date;
            $leave_national->status = $request->status;
            $leave_national->modified_by = $employee->id;
            $leave_national->save();

            if ($leave_national->holiday_date != $leave_national->current_date) {
                $existsRequests = LeaveRequest::where('leave_type_id', $leave_national->leave_type_id)->where('from', $leave_national->holiday_date)->get();
                if (count($existsRequests) > 0) {
                    foreach ($existsRequests as $existsRequest) {
                        if ($leave_national->status == 0) {
                            $existsRequest->deleted_by = $employee->id;
                            $existsRequest->save();
                            $existsRequest->delete();
                        } else {
                            $existsRequest->from = $request->date;
                            $existsRequest->modified_by = $employee->id;
                            $existsRequest->save();
                        }
                    }
                }
            }

            return ResponseWithSuccessData($lang, $leave_national, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
