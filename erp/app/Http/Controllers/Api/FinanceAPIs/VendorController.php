<?php

namespace App\Http\Controllers\Api\FinanceAPIs;

use App\Helper\APIResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreateVendorRequest;
use App\Http\Resources\Finance\VendorResource;
use App\Services\FinanceServices\VendorService;
use App\Models\CostCenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{

    protected $vendorService;

    public function __construct(VendorService $vendorService)
    {
        $this->vendorService = $vendorService;
    }

    public function store(CreateVendorRequest $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            $costCenter = $this->vendorService->add($request);
            return ResponseWithSuccessData($lang, new VendorResource($costCenter), 1);
        } catch (\Exception $e) {
            return respondError(
                $lang == 'en' ? 'An error occurred' : 'حصل خطأ',
                400,
                $lang == 'en'
                    ? ['An error occurred during the addition process. Please try again.']
                    : ['حصل خطأ أثناء عملية الإضافة من فضلك حاول مرة أخرى']
            );
        }
    }

    public function list()
    {
        $lang = request()->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        return $data = $this->vendorService->list(request());
    }
}
