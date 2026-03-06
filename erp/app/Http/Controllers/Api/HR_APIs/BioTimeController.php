<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Services\HR_Services\BioTimeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class BioTimeController extends Controller
{
    protected $bioTimeService;

    public function __construct(BioTimeService $bioTimeService)
    {
        $this->bioTimeService = $bioTimeService;
    }
    /**
     * Clock in/out API endpoint
     */
    public function clockInOut(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $validator = Validator::make($request->all(), [
            'punch_state' => 'required|integer|in:0,1', // 0 for clock in, 1 for clock out
        ], [], [
            'punch_state' => __('validation.attributes.punch_state'),
        ]);
        if ($validator->fails()) {
            $message = $lang == 'en' ? 'Invalid punch state' : 'حالة التوقيت غير صالحة';
            return respondError($message, 400, $validator->errors());
        }

        $result = $this->bioTimeService->processPunch($request, $request->punch_state, $lang);

        return $result;
    }

    // public function authenticate(Request $request)
    // {
    //     $request->validate([
    //         'username' => 'required|string',
    //         'password' => 'required|string',
    //     ]);

    //     $token = $this->bioTimeService->getJwtAuthToken($request->username, $request->password);

    //     if ($token) {
    //         return response()->json(['token' => $token], 200);
    //     }

    //     return response()->json(['message' => 'Authentication failed'], 401);
    // }
}
