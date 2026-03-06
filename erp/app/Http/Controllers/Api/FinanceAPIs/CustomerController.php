<?php

namespace App\Http\Controllers\Api\FinanceAPIs;

use App\Helper\APIResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreateCustomerRequest;
use App\Http\Resources\Finance\CustomerResource;
use App\Services\FinanceServices\CustomerService;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{

    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function store(CreateCustomerRequest $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            $customer = $this->customerService->add($request);
            return ResponseWithSuccessData($lang, new CustomerResource($customer), 1);
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
        return $data = $this->customerService->list(request());
    }
}
