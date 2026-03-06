<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchMenu;
use App\Models\ClientAddress;
use App\Models\Country;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderTracking;
use App\Models\OrderTransaction;
use App\Models\Table;
use App\Models\User;
use App\Services\SettingsServices\BranchService;
use App\Services\CustomerReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CustomerReportController extends Controller
{
    protected $customerReportService;
    protected $lang;
    protected $checkToken;  // Set to true or false based on your need
    protected $branchService;


    public function __construct(BranchService $branchService)
    {
        // $this->customerReportService = $customerReportService;
        // $this->lang =  app()->getLocale();
        $this->checkToken = false;
        $this->branchService = $branchService;
    }

    // public function clients(Request $request)
    // {
    //     $response = $this->customerReportService->all_clients($request, $this->checkToken);
    //     $responseData = $response->original;
    //     $clients = $responseData['data'];
    //     return view('dashboard.reports.clients', compact('clients'));
    // }

    // public function show_client_reports(Request $request)
    // {
    //     $response = $this->customerReportService->index($request, $this->checkToken);
    //     $responseData = $response->original;
    //     $orders = $responseData['data'];
    //     return view('dashboard.reports.list', compact('orders'));
    // }

    // public function show($id)
    // {
    //     $lang = App::getLocale(); // Get the current locale

    //     $response = $this->customerReportService->show($lang, $id, $this->checkToken);

    //     $responseData = $response->original;

    //     $order = $responseData['data'];

    //     return view('dashboard.reports.show', compact('order'));
    // }

    // public function showInvoice($id)
    // {
    //     $lang = App::getLocale(); // Get the current locale

    //     $response = $this->customerReportService->show($lang, $id, $this->checkToken);

    //     $responseData = $response->original;

    //     $order = $responseData['data'];

    //     return view('dashboard.reports.invoice', compact('order'));
    // }

    public function printReceipt($id, $type)
    {
        $order = Order::with(['orderDetails.dish', 'orderAddons.Addon.addons', 'client', 'address'])->findOrFail($id);

        switch ($type) {
            case 'customer_delivery_print':
                return view('dashboard.reports.customer_delivery_print', compact('order'));
            case 'customer_dinein_print':
                return view('dashboard.reports.customer_dinein_print', compact('order'));
            case 'customer_takeaway_print':
                return view('dashboard.reports.customer_takeaway_print', compact('order'));
            case 'delivery_print':
                return view('dashboard.reports.delivery_print', compact('order'));
            default:
                abort(404, 'Invalid print type');
        }
    }

    public function downloadOrder($id)
    {
        $lang = App::getLocale(); // Get the current locale
        $response = $this->customerReportService->show($lang, $id, $this->checkToken);
        $responseData = $response->original;
        $order = $responseData['data'];
        $URL = URL::to('/');
        $order['qr'] = QrCode::format('png')->size(80)->errorCorrection('H')->generate(route('order.change.status', $order->id));
        $order['qr'] = base64_encode($order['qr']);  // base64 encoding the PNG image

        // Generate QR code as a string (SVG format)
        // $order['site_logo'] = $URL . '/build/assets/images/brand-logos/desktop.png';
        $order['site_logo'] = asset('build/assets/images/brand-logos/desktop.png');

        // Load the PDF view with the order and QR code
        $pdf = Pdf::loadView('dashboard.reports.pdf', compact('order'));
        return $pdf->download($order->order_number . '.pdf');

        // Stream the generated PDF (you can also download it using download() method)
        // return $pdf->stream();
    }


    public function mostCustomers(Request $request)
    {
        $lang = app()->getLocale();
        $from = $request->input('from');
        $to = $request->input('to');
        $order = $request->input('order_by');
        $query  = Order::with([
            'Client' => function ($query) {
                $query->withTrashed();
            },
            'Branch',
            'address',
            'orderDetails',
            'orderTransactions'
        ])
            ->select(
                'client_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_price_after_tax) as total_price'),
                DB::raw('GROUP_CONCAT(id) as order_ids')
            )
            ->whereHas('orderTransactions', function ($subQuery) {
                $subQuery->where('payment_status', 'paid');
            })
            ->groupBy('client_id');

        if ($from && $to) {
            $query->whereBetween('date', [$from, $to]);
        } elseif ($from) {
            $query->whereDate('date', '>=', $from);
        } elseif ($to) {
            $query->whereDate('date', '<=', $to);
        }

        if ($order == 1) {
            $query->orderByDesc('total_orders');
        } elseif ($order == 2) {
            $query->orderByDesc('total_price');
        }

        $topClients = $query->get();

        return view('dashboard.reports.customers.bestcustomer', compact('topClients'));
    }
    public function mostcustomerdetail(Request $request, $id)
    {
        $lang = app()->getLocale();

        $from = $request->input('from');
        $to = $request->input('to');
        // Client Order Summary
        $Clientdetailquary = Order::with(['Client' => function ($query) {
            $query->withTrashed();
        }, 'Branch', 'address', 'orderDetails', 'orderTransactions'])
            ->select(
                'client_id',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total_price_after_tax) as total_price'),
                DB::raw('GROUP_CONCAT(id) as order_ids')
            )
            ->whereHas('orderTransactions', function ($subQuery) {
                $subQuery->where('payment_status', 'paid');
            })
            ->where('client_id', $id)
            ->groupBy('client_id');

        // All orders for this client
        $ordersquary = Order::with('orderDetails', 'Branch', 'address', 'orderTransactions')
            ->where('client_id', $id)
            ->whereHas('orderTransactions', function ($subQuery) {
                $subQuery->where('payment_status', 'paid');
            });

        // Most Used Payment Method
        $mostUsedPaymentMethodquary = OrderTransaction::select('payment_method', DB::raw('COUNT(*) as method_count'))
            ->where('payment_status', 'paid')
            ->whereHas('order', function ($query) use ($id) {
                $query->where('client_id', $id);
            })
            ->groupBy('payment_method')
            ->orderByDesc('method_count');

        // Most Ordered Branch
        $query = Order::select('branch_id', DB::raw('COUNT(*) as order_count'))
            ->where('client_id', $id)
            ->whereHas('orderTransactions', function ($subQuery) {
                $subQuery->where('payment_status', 'paid');
            })
            ->groupBy('branch_id')
            ->orderByDesc('order_count');

        // Date Filtering
        if ($from && $to) {
            $query->whereBetween('date', [$from, $to]);
            $Clientdetailquary->whereBetween('date', [$from, $to]);
            $ordersquary->whereBetween('date', [$from, $to]);
            $mostUsedPaymentMethodquary->whereBetween('created_at', [$from, $to]);
        } elseif ($from) {
            $query->whereDate('date', '>=', $from);
            $Clientdetailquary->whereDate('date', '>=', $from);
            $ordersquary->whereDate('date', '>=', $from);
            $mostUsedPaymentMethodquary->whereDate('created_at', '>=', $from);
        } elseif ($to) {
            $query->whereDate('date', '<=', $to);
            $Clientdetailquary->whereDate('date', '<=', $to);
            $ordersquary->whereDate('date', '<=', $to);
            $mostUsedPaymentMethodquary->whereDate('created_at', '<=', $to);
        }

        // Get Results
        $mostOrderedBranch = $query->first();
        $Clientdetail = $Clientdetailquary->first();
        $orders = $ordersquary->get();
        $mostUsedPaymentMethod = $mostUsedPaymentMethodquary->first();
        // Default Address
        $defaultAddress = ClientAddress::where('user_id', $id)
            ->where('is_default', 1)
            ->first();

        // Main Branch
        $mainbranch = Branch::where('is_default', 1)->first();
        if ($mainbranch) {
            $mainbranchName = ($lang === "ar") ? $mainbranch->name_ar : $mainbranch->name_en;
            $mainbranchAddress = ($lang === "ar") ? $mainbranch->address_ar : $mainbranch->address_en;
        }
        // Formatted Date
        $today = Carbon::now();
        // $today->locale(App::getLocale());
        $formattedDate = $today->formatLocalized('%d, %b %Y');

        // Branch Details for the Most Ordered Branch
        $branchDetail = null;
        if ($mostOrderedBranch) {
            $branchDetail = Branch::find($mostOrderedBranch->branch_id);
            if ($branchDetail) {
                $branchName = ($lang === "ar") ? $branchDetail->name_ar : $branchDetail->name_en;
                $branchAddress = ($lang === "ar") ? $branchDetail->address_ar : $branchDetail->address_en;
            }
        }

        return view('dashboard.reports.customers.bestcustomer-detail', compact(
            'mostUsedPaymentMethod',
            'Clientdetail',
            'orders',
            'defaultAddress',
            'branchDetail',
            'formattedDate',
            'branchName',
            'branchAddress',
            'mainbranch',
            'mainbranchName',
            'mainbranchAddress'
        ));
    }
}
