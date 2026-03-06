<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\HR_Services\HolidaysService;

class HolidayController extends Controller
{
    protected $holidaysService;

    public function __construct(HolidaysService $holidaysService)
    {
        $this->holidaysService = $holidaysService;
    }
    /**
     * Show calendar view with public holidays for selected country.
     */
    public function showCalendar(Request $request)
    {
        $countryCode = $request->get('country', 'eg');
        $weekendDays = $this->getCountryWeekendDays($countryCode);
        $holidays = [];
        $useEnglish = $request->get('lang', 'ar') === 'en';
        $date = now();
        try {
            $events = $this->holidaysService->getHolidays($countryCode, $date, $useEnglish);

            return view('dashboard.calendar.holidays', [
                'events' => $events,
                'selectedCountry' => $countryCode,
                'weekendDays' => $weekendDays,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch holidays: ' . $e->getMessage());

            return view('dashboard.calendar.holidays', [
                'events' => [],
                'selectedCountry' => $countryCode,
                'weekendDays' => $weekendDays,
            ]);
        }
    }

    /**
     * Define weekend days based on country.
     * For many Arab countries, the weekend is Friday and Saturday.
     */
    private function getCountryWeekendDays($countryCode)
    {
        $fridaySaturdayCountries = ['eg', 'kw', 'ae', 'saudiarabian'];
        return in_array($countryCode, $fridaySaturdayCountries) ? [5, 6] : [0, 6]; // 0 = Sunday, 6 = Saturday
    }

    /**
     * Get Google public holiday calendar ID based on country and language.
     */
    
}
