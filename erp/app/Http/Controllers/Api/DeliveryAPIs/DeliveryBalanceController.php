<?php

namespace App\Http\Controllers\Api\DeliveryAPIs;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Country;
use App\Models\Order;
use App\Services\HR_Services\TimetableService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class DeliveryBalanceController extends Controller
{
    protected $timeTableService;

    public function __construct(TimetableService $timeTableService)
    {
        $this->timeTableService = $timeTableService;
    }

    public function getCurrent(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $user = $this->getAuthenticatedDriver($lang);
            if (!$user) return RespondWithBadRequest($lang, 4);

            App::setLocale($lang);

            $this->setUserShiftDetails($user);
            $currencySymbol = $this->getUserCurrencySymbol($user);

            $today = Carbon::now()->format('Y-m-d');
            $start = $today . ' ' . $user->shift_start;
            $end = $today . ' ' . $user->shift_end;

            $cashTotal = $this->getOrderTotal($user->id, $start, $end, 'cash');
            $visaTotal = $this->getOrderTotal($user->id, $start, $end, 'credit_card');

            $orderStats = $this->getOrderStatistics($user->id, $start, $end);

            return ResponseWithSuccessData($lang, [
                'shift_start' => $user->shift_start,
                'shift_end' => $user->shift_end,
                'total_cash' => $cashTotal,
                'total_visa' => $visaTotal,
                'total_orders' => $orderStats['total'],
                'cancelled_orders' => $orderStats['cancelled'],
                'currency_symbol' => $currencySymbol,
            ], 1);

        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    public function closeBalance(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $user = $this->getAuthenticatedDriver($lang);
            if (!$user) return RespondWithBadRequest($lang, 4);

            App::setLocale($lang);

            $validator = Validator::make($request->all(), [
                'close_cash' => 'required|numeric|min:0',
                'close_visa' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return respondError($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.', 400, $validator->errors());
            }

            $this->setUserShiftDetails($user);

            if (!$user->shift_start || !$user->shift_end) {
                return respondErrorData(
                    $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                    400,
                    $lang == 'en' ? ['Employee shift does not exist.'] : ['شيفت الموظف غير موجود.']
                );
            }

            if ($request->close_cash !== null) {
                return RespondWithSuccessRequest($lang, 1);
            }

            return respondErrorData(
                $lang == 'en' ? 'Matching balance Error.' : 'خطأ في مطابقة الرصيد.',
                400,
                $lang == 'en'
                    ? ['The amounts entered do not match the system records or expected totals.']
                    : ['المبالغ المدخلة لا تتوافق مع سجلات النظام او الإجماليات المتوقعة.']
            );

        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    // local helper Methods
    private function getAuthenticatedDriver($lang)
    {
        $user = auth('employee')->user();
        return ($user && $user->flag == 'driver') ? $user : null;
    }

    private function setUserShiftDetails(&$user)
    {
        $today = Carbon::now()->format('Y-m-d');
        $shift = $this->timeTableService->getTimetableForDate($user->id, $today);

        if ($shift['status'] !== false) {
            $lang = App::getLocale();
            $user->shift_start = $shift['data']['on_duty_time'];
            $user->shift_end = $shift['data']['off_duty_time'];
            $user->shift_type = $lang == 'en'
                ? $shift['data']['timetable']['name_en']
                : $shift['data']['timetable']['name_ar'];
            $user->employee_schedule_id = $shift['data']['employee_schedule_id'];
        } else {
            $user->shift_start = null;
            $user->shift_end = null;
            $user->shift_type = null;
            $user->employee_schedule_id = null;
        }
    }

    private function getUserCurrencySymbol($user)
    {
        $country = Branch::find($user->branch_id)->country_id ?? null;
        return Country::find($country)->currency_symbol ?? null;
    }

    private function getOrderTotal($userId, $start, $end, $paymentMethod)
    {
        return Order::where('delivery_id', $userId)
            ->whereBetween('updated_at', [$start, $end])
            ->where('status', 'completed')
            ->where('print_status', 'done')
            ->whereHas('transaction', function ($query) use ($paymentMethod) {
                $query->where('payment_method', $paymentMethod)
                    ->where('payment_status', 'paid');
            })
            ->sum('total_price_after_tax');
    }

    private function getOrderStatistics($userId, $start, $end)
    {
        return [
            'total' => Order::where('delivery_id', $userId)
                ->whereBetween('updated_at', [$start, $end])
                ->count(),
            'cancelled' => Order::where('delivery_id', $userId)
                ->whereBetween('updated_at', [$start, $end])
                ->where('status', 'cancelled')
                ->count()
        ];
    }
}
