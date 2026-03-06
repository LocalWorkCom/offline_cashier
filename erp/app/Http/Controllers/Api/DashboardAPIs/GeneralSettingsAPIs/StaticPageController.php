<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfferResource;
use App\Http\Resources\StaticPageResource;
use App\Models\Offer;
use App\Models\PolicyPaymentReservation;
use App\Models\PrivacyPolicy;
use App\Models\ReturnPolicy;
use App\Models\TermsAndCondition;
use App\Models\FAQ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaticPageController extends Controller
{
    private $lang;

    public function __construct(Request $request)
    {
        $this->lang = $request->header('lang', 'ar');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page = $request->query('page');
        $response = null;
        switch ($page) {
            case 'terms':
                $terms = TermsAndCondition::where('active', 1)->orderBy('created_at', 'asc')->get();
                $response = $terms->isEmpty() ? null : StaticPageResource::collection($terms);
                break;

            case 'privacy':
                $privacies =  PrivacyPolicy::where('active', 1)->orderBy('created_at', 'asc')->get();
                $response = $privacies->isEmpty() ? null : StaticPageResource::collection($privacies);
                break;

            case 'return':
                $returns = ReturnPolicy::where('active', 1)->orderBy('created_at', 'asc')->get();
                $response = $returns->isEmpty() ? null : StaticPageResource::collection($returns);
                break;

            case 'faqs':
                $faqs = FAQ::where('active', 1)->orderBy('created_at', 'asc')->get();
                $response = $faqs->isEmpty() ? null : StaticPageResource::collection($faqs);
                break;

            case 'reservation':
                $paymentPolicies = PolicyPaymentReservation::first();

                $response = [
                    [
                        'name' => 'payment',
                        'value' => strip_tags($paymentPolicies->payment ?? null),
                    ],
                    [
                        'name' => 'reservation',
                        'value' => strip_tags($paymentPolicies->reservation ?? null),
                    ],
                ];
                break;

            default:
                return response()->json([
                    'status' => true,
                    'message' => $this-> lang == 'en' ? 'Failed Message' : 'طلب غير صحيح',
                    'errorData' => ['error' => $this->lang == 'en' ? ['Failed Message'] : ['طلب غير صحيح']] ,
                    'code' => 400,
                    'data'   => null
                ], 200);
        }
        return ResponseWithSuccessData($this->lang, $response, 1);
    }

}
