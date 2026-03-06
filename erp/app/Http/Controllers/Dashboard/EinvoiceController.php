<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CashierMachine;
use App\Models\CashierSetting;
use App\Models\CashierSettingLog;
use App\Models\Einvoice;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EinvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Show the form for creating a new resource.
     */
    public function setting()
    {
        $data =  CashierSetting::find(1);
        return view('dashboard.einvoice.setting', compact('data'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $fromDate = $request->input('from');
        $toDate = $request->input('to');
        $cashierMachineId = $request->input('cashier_machine_id');
        $paymentMethod = $request->input('payment_method'); // new

        $cashierMachines = CashierMachine::all();

        if (!$cashierMachineId && $cashierMachines->isNotEmpty()) {
            $cashierMachineId = $cashierMachines->first()->id;
        }

        $einvoicesQuery = Einvoice::with(['invoice.orders.cashierMachine', 'invoice.orders.transaction']) // make sure transaction is eager loaded
            ->whereNull('submissionId');
        // Default to last 24 hours if no dates provided
        if (!$fromDate || !$toDate) {
            $startDate = Carbon::now()->subDay();
            $endDate = Carbon::now();
        } else {
            $startDate = Carbon::parse($fromDate)->startOfDay();
            $endDate = Carbon::parse($toDate)->endOfDay();
        }

        // Filter by related order's created_at and optional cashier/payment method
        $einvoicesQuery->whereHas('invoice.orders', function ($query) use ($startDate, $endDate, $cashierMachineId, $paymentMethod) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed');

            if ($cashierMachineId) {
                $query->where('cashier_machine_id', $cashierMachineId);
            }

            if ($paymentMethod) {
                $query->whereHas('transaction', function ($subQuery) use ($paymentMethod) {
                    $subQuery->where('payment_method', $paymentMethod);
                });
            }
        });

        $einvoices = $einvoicesQuery->get();

        return view('dashboard.einvoice.show', compact(
            'einvoices',
            'cashierMachines',
            'cashierMachineId',
            'fromDate',
            'toDate',
            'paymentMethod'
        ));
    }


    public function submitted(Request $request)
    {
        $fromDate = $request->input('from');
        $toDate = $request->input('to');
        $cashierMachineId = $request->input('cashier_machine_id');

        $cashierMachines = CashierMachine::all();

        if (!$cashierMachineId && $cashierMachines->isNotEmpty()) {
            $cashierMachineId = $cashierMachines->first()->id;
        }

        // Fetch invoices with their related orders and cashier machines
        $einvoicesQuery = Einvoice::with(['invoice.orders.cashierMachine', 'invoice.orders.orderTransactions'])->whereNotNull('submissionId');
        if ($fromDate && $toDate) {
            $einvoicesQuery->whereBetween('date', [$fromDate, $toDate]);
        }

        if ($cashierMachineId) {
            $einvoicesQuery->whereHas('invoice.orders', function ($query) use ($cashierMachineId) {
                $query->where('cashier_machine_id', $cashierMachineId);
            });
        }

        // Get the invoices
        $einvoices = $einvoicesQuery->get();

        return view('dashboard.einvoice.submitted', compact('einvoices', 'cashierMachines', 'cashierMachineId', 'fromDate', 'toDate'));
    }

    public function filterInvoices(Request $request)
    {
        $cashierMachineId = $request->input('cashier_machine_id');
        $cashierMachines = CashierMachine::all();
        $fromDate = $request->input('from');
        $toDate = $request->input('to');

        $cashierMachine = CashierMachine::find($cashierMachineId);
        if (!$cashierMachine) {
            return redirect()->back()->with('error', 'Invalid Cashier Machine ID.');
        }

        $cashierSettings = $cashierMachine->cashierSettings()->first();
        if (!$cashierSettings) {
            return redirect()->back()->with('error', 'No settings found for this cashier machine.');
        }

        $settings = [
            'min_no_of_invoices' => $cashierSettings->min_count,
            'max_no_of_invoices' => $cashierSettings->max_count,
            'min_amount_of_money' => $cashierSettings->min_balance,
            'max_amount_of_money' => $cashierSettings->max_balance,
        ];

        // Get all orders with their transactions
        $orders = Einvoice::with(['order.orderTransactions'])
            ->whereHas('order', function ($query) use ($cashierMachineId) {
                $query->where('cashier_machine_id', $cashierMachineId);
            })
            ->when($fromDate, function ($query) use ($fromDate) {
                $query->where('created_at', '>=', $fromDate);
            })
            ->when($toDate, function ($query) use ($toDate) {
                $query->where('created_at', '<=', $toDate);
            })
            ->get()
            ->pluck('order')
            ->filter();

        if ($orders->isEmpty()) {
            return redirect()->back()->with('error', 'No invoices found.');
        }

        // Prepare invoice data with electronic payment flag
        $invoiceData = $orders->map(function ($order) {
            $hasElectronicPayment = $order->orderTransactions->contains(function ($transaction) {
                return in_array($transaction->payment_method, ['credit', 'online', 'credit_with_delivery']);
            });

            return [
                'inv_number' => $order->id,
                'amount' => $order->total_price_after_tax,
                'is_electronic' => $hasElectronicPayment
            ];
        })->toArray();

        $filteredInvoices = getFilteredInvoices($invoiceData, $settings);

        if (empty($filteredInvoices)) {
            return redirect()->back()->with('error', 'No invoices met the filter criteria.');
        }

        $filteredOrders = $orders->filter(function ($order) use ($filteredInvoices) {
            return in_array($order->id, $filteredInvoices);
        });

        return view('dashboard.einvoice.show', compact(
            'filteredOrders',
            'fromDate',
            'toDate',
            'cashierMachines',
            'cashierMachineId'
        ));
    }

    public function testInvoices(Request $request)
    {
        $cashierMachines = CashierMachine::all();
        $fromDate = $request->input('from');
        $toDate = $request->input('to');
        $selectedInvoices = explode(',', $request->selected_invoices[0]);

        if (empty($selectedInvoices)) {
            return redirect()->back()->with('error', 'No invoices selected.');
        }

        // Load orders with their transactions to check payment methods
        $orders = Einvoice::with(['invoice.orders.orderTransactions'])
            ->whereIn('id', $selectedInvoices)
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->back()->with('error', 'No matching invoices found.');
        }

        $cashierMachineId = $request->input('cashier_machine_id');
        $cashierMachine = CashierMachine::find($cashierMachineId);

        if (!$cashierMachine) {
            return redirect()->back()->with('error', 'Invalid Cashier Machine ID.');
        }

        $cashierSettings = $cashierMachine->cashierSettings()->first();

        if (!$cashierSettings) {
            return redirect()->back()->with('error', 'No settings found for this cashier machine.');
        }

        $settings = [
            'min_no_of_invoices' => $cashierSettings->min_count,
            'max_no_of_invoices' => $cashierSettings->max_count,
            'min_amount_of_money' => $cashierSettings->min_balance,
            'max_amount_of_money' => $cashierSettings->max_balance,
        ];

        // Prepare invoice data with electronic payment flag
        $invoiceData = $orders->map(function ($order) {
            $hasElectronicPayment = $order->order->orderTransactions->contains(function ($transaction) {
                return in_array($transaction->payment_method, ['credit', 'online', 'credit_with_delivery']);
            });

            return [
                'inv_number' => $order->id,
                'amount' => $order->order->total_price_after_tax,
                'is_electronic' => $hasElectronicPayment
            ];
        })->toArray();

        $filteredInvoices = getFilteredInvoices($invoiceData, $settings);

        if (empty($filteredInvoices)) {
            return redirect()->back()->with('error', 'No invoices met the filter criteria.');
        }

        $filteredOrders = $orders->filter(function ($order) use ($filteredInvoices) {
            return in_array($order->id, $filteredInvoices);
        });

        return view('dashboard.einvoice.show', compact(
            'filteredOrders',
            'cashierMachines',
            'fromDate',
            'toDate',
            'cashierMachineId'
        ));
    }
    public function resetInvoices(Request $request)
    {
        return redirect()->route('dashboard.einvoices.show', [
            'cashier_machine_id' => $request->input('cashier_machine_id'),
        ]);
    }

    // public function uploadInvoices(Request $request)
    // {
    //     $selectedInvoices = $request->selected_invoices;
    //     $orders = Order::whereIn('id', $selectedInvoices)->get();

    //     foreach ($orders as $order) {
    //         $this->sendEinvoiceToPortal($order);
    //     }

    //     return redirect()->back()->with('message', __('Invoices uploaded successfully'));
    // }
    public function getCashierSettings($cashierMachineId)
    {
        $cashierMachine = CashierMachine::find($cashierMachineId);

        if (!$cashierMachine) {
            return response()->json([
                'success' => false,
                'message' => 'Cashier machine not found.',
            ]);
        }

        $cashierSettings = $cashierMachine->cashierSettings()->first();

        if (!$cashierSettings) {
            return response()->json([
                'success' => false,
                'message' => 'No settings found for this cashier machine.',
            ]);
        }

        return response()->json([
            'success' => true,
            'settings' => [
                'min_balance' => $cashierSettings->min_balance,
                'max_balance' => $cashierSettings->max_balance,
                'min_count' => $cashierSettings->min_count,
                'max_count' => $cashierSettings->max_count,
            ],
        ]);
    }

    function loginTaxes()
    {
        $client = new Client();
        if (einvoice_settings('tax_live') == 0) {
            $url = einvoice_settings('idSrvBaseUrlPreprodEg');
        } else {
            $url = einvoice_settings('idSrvBaseUrlProdEg');
        }
        try {

            $response = $client->request('POST', $url . '/connect/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => einvoice_settings('tax_client_id'),
                    'client_secret' => einvoice_settings('tax_secret_id'),
                    'scope' => 'InvoicingAPI'
                ]
            ]);
            $response = json_decode($response->getBody()->getContents(), true);
            return $response;
        } catch (GuzzleException $exception) {
            // if response is invaild
            $response = json_decode($exception->getResponse()->getBody(true)->getContents(), true);
            // $invoice->error_msg = $errors;
            // $invoice->save();
            return $response;
        }
    }
}
