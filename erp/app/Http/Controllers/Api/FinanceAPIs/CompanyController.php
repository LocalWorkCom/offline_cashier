<?php

namespace App\Http\Controllers\Api\FinanceAPIs;

use App\Helper\APIResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\CompanyResource;
use App\Services\FinanceServices\CompanyService;
use App\Models\CompanyProfileSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{

    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        $data = $this->companyService->getAll($request);
        $hidden = ['socialMediaInformation', 'companyPolicy', 'branch', 'contactInformationSetting'];
        $response = paginateOrGetAll($data, $request, $hidden, []);

        // return response()->json([
        //     'data' => CompanyResource::collection($response['data'])->additional(['withRelations' => false]),
        //     'meta' => $response['meta'],
        //     'status' => true,
        //     'message' => ApiCode(1)->{'api_code_message_' . ($lang == 'ar' ? 'ar' : 'en')},
        //     'code' => 200,
        // ]);

        $items = $response['data']->map(function ($company) {
            return (new CompanyResource($company, false))->toArray(request());
        });

        return response()->json([
            'data' => $items,
            'meta' => $response['meta'],
            'status' => true,
            'message' => ApiCode(1)->{'api_code_message_' . ($lang == 'ar' ? 'ar' : 'en')},
            'code' => 200,
        ]);
    }

    public function show($id)
    {
        $lang = request()->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        return $data = $this->companyService->show(request());
    }

    public function list()
    {
        $lang = request()->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        return $data = $this->companyService->list(request());
    }

}

