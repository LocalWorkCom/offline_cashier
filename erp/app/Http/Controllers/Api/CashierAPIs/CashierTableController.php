<?php

namespace App\Http\Controllers\Api\CashierAPIs;

use Pusher\Pusher;
use App\Models\Order;
use Knp\Snappy\Image;
use App\Models\Client;
// use App\Events\dishChangeStatus;
use App\Models\Coupon;
use ArPHP\I18N\Arabic;
use App\Models\Invoice;
use App\Models\Employee;
use App\Events\TotalPaid;
use App\Traits\ChatTrait;
use App\Models\BranchMenu;
use App\Models\OrderAddon;
use Mike42\Escpos\Printer;
use App\Events\TableStatus;

use App\Models\OrderDetail;
use App\Models\DishCategory;
use Illuminate\Http\Request;

use App\Models\OrderTracking;
use App\Models\InvoiceDetails;
use Illuminate\Support\Carbon;
use Mike42\Escpos\EscposImage;
use App\Models\BranchMenuAddon;
use App\Models\EmployeeMachine;
use Illuminate\Validation\Rule;
use App\Events\dishChangeStatus;
use App\Models\OrderTransaction;
// use App\Http\Controllers\Controller;
use App\Events\dishChangeStatus2;
use App\Events\orderChangeStatus;
use App\Traits\DishCategoryTrait;
use App\Models\BranchMenuCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\MenusIntegrationDishSize;
use Illuminate\Support\Facades\Validator;
use App\Services\ClientServices\TipService;
use App\Services\ClientServices\OrderService;
use App\Services\HR_Services\TimetableService;
use App\Services\ClientServices\InvoiceService;
use App\Services\AddressServices\BranchSiteService;
use Intervention\Image\ImageManagerStatic as ImageManager;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use App\Http\Controllers\Api\CashierAPIs\CashierInvoiceController;


class CashierTableController extends Controller
{
    use ChatTrait, DishCategoryTrait;

    protected $orderService;
    protected $employee;
    protected $tipService;
    protected $invoiceService;
    protected $branchSiteService;

    public function __construct(OrderService $orderService, InvoiceService $invoiceService, TipService $tipService, BranchSiteService $branchSiteService)
    {
        $this->tipService = $tipService;
        $this->orderService = $orderService;
        $this->invoiceService = $invoiceService;
        $this->branchSiteService = $branchSiteService;
        $this->employee = auth('employee')->user();
    }

    public function TableorderDetails(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $today = Carbon::today();

        $shiftDetails = TimetableService::getTimetableForDate($this->employee->id, $today);

        $orderId = Order::where('branch_id', $this->employee->branch_id)
            ->where('table_id', $request->table_id)
            ->orderBy('id', 'desc')
            ->first()->id;


        $response = [
            'order_id' => $orderId,
        ];

        return ResponseWithSuccessData($lang, $response, 1);
    }

      public function changeOrderTable(Request $request)
        {
            $lang = $request->header('lang', 'ar');
            App::setLocale($lang);

            $employee = auth('employee')->user();
            $created_by = $employee->id;

            $validator = Validator::make($request->all(), [
                'order_id' => 'required|exists:orders,id',
                'table_id' => 'required|exists:tables,id',
            ]);

            if ($validator->fails()) {
                return respondErrorData('Validation Errors', 400, $validator->errors());
            }

            $result = $this->orderService->changeOrderTable($request->table_id, $request->order_id);

            if ($result === true) {
                // response success
            return ResponseWithSuccessData($lang, $result, 1);

            }

            if ($result instanceof \Illuminate\Http\JsonResponse) {
                $data = $result->getData(true);
                return response()->json([
                    'status'  => false,
                    'message' => $data['message'] ?? __('validation.cannotchange'),
                ], 400);

            }


          return response()->json([
                'status'  => false,
                'message' => __('validation.cannotchange'),
            ], 400);

        }




}
