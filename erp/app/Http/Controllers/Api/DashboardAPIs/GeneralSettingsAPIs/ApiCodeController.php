<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;

use App\Models\Product;
use App\Models\ApICode;

use Illuminate\Http\Request;

class ApiCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided

        $api_codes = ApICode::all();
        return ResponseWithSuccessData($lang, $api_codes, 1);
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided

        $api_code_title_ar = $request->api_code_title_ar;
        $api_code_title_en = $request->api_code_title_en;

        $api_code_message_ar  = $request->api_code_message_ar;
        $api_code_message_en = $request->api_code_message_en;

        $api_code  = new ApICode();
        $api_code->api_code_title_ar = $api_code_title_ar;
        $api_code->api_code_title_en = $api_code_title_en;
        $api_code->api_code_message_ar = $api_code_message_ar;
        $api_code->api_code_message_en = $api_code_message_en;
        $api_code->code = GetLastID('apicodes') + 1;
        $api_code->save();


        return RespondWithSuccessRequest($lang, 1);
    }
}
