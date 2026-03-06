<?php

namespace App\Services;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;
use App\Models\Order;
use App\Models\OrderTransaction;
use App\Models\OrderTracking;

use App\Models\TableReservation;
use App\Models\TableReservationTransaction;

use MyFatoorah\Library\MyFatoorah;
use MyFatoorah\Library\API\Payment\MyFatoorahPayment;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentEmbedded;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentStatus;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MyFatoorahService extends Controller
{

    /**
     * @var array
     */
    public $mfConfig = [];

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Initiate MyFatoorah Configuration
     */
    public function __construct()
    {
        $this->mfConfig = [
            'apiKey'      => config('myfatoorah.api_key'),
            'isTest'      => config('myfatoorah.test_mode'),
            'countryCode' => config('myfatoorah.country_iso'),
        ];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Redirect to MyFatoorah Invoice URL
     * Provide the index method with the order id and (payment method id or session id)
     *
     * @return Response
     */
    public function index($orderId, $type, $reserve)
    {
        try {
            //For example: pmid=0 for MyFatoorah invoice or pmid=1 for Knet in test mode
            $paymentId = request('pmid') ?: 0;
            $sessionId = request('sid') ?: null;

            //$orderId  = request('oid') ?: 147;
            $curlData = $this->getPayLoadData($orderId, $type, $reserve);

            $mfObj   = new MyFatoorahPayment($this->mfConfig);
            $payment = $mfObj->getInvoiceURL($curlData, $paymentId, $orderId, $sessionId);

            return $links = array('link' => $payment['invoiceURL']);
            //return $payment['invoiceURL'];
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            return response()->json(['IsSuccess' => 'false', 'Message' => $exMessage]);
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Example on how to map order data to MyFatoorah
     * You can get the data using the order object in your system
     *
     * @param int|string $orderId
     *
     * @return array
     */
    private function getPayLoadData($orderId = null, $type, $reserve)
    {
        $callbackURL = route('myfatoorah.callback');

        //You can get the data using the order object in your system
        $order = $this->getTestOrderData($orderId, $reserve);

        return [
            'CustomerName'       => $order['customer_name'],
            'InvoiceValue'       => $order['total'],
            'DisplayCurrencyIso' => $order['currency'],
            'CustomerEmail'      => $order['customer_email'],
            'CallBackUrl'        => $callbackURL,
            'ErrorUrl'           => $callbackURL,
            'MobileCountryCode'  => $order['mobile_country_code'],
            'CustomerMobile'     => $order['customer_mobile'],
            'Language'           => $order['lang'],
            'CustomerReference'  => $orderId,
            'UserDefinedField'   => json_encode(['type' => $type, 'reserve' => $reserve]),
            'SourceInfo'         => 'Laravel ' . app()::VERSION . ' - MyFatoorah Package ' . MYFATOORAH_LARAVEL_PACKAGE_VERSION
        ];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get MyFatoorah Payment Information
     * Provide the callback method with the paymentId
     *
     * @return Response
     */
    public function callback($paymentId)
    {
        try {
            //$paymentId = request('paymentId');

            $mfObj = new MyFatoorahPaymentStatus($this->mfConfig);
            $data  = $mfObj->getPaymentStatus($paymentId, 'PaymentId');

            $order_info = json_decode($data->UserDefinedField);

            $message = $this->getTestMessage($data->InvoiceStatus, $data->InvoiceError);

            $user = Auth::guard('client')->user();
            //update order status
            if ($order_info->reserve == "without") {
                $order = TableReservation::where('id', $data->CustomerReference)->first();
                if ($data->InvoiceStatus != "Paid") {
                    $order->status = "cancel";
                }
                $order->modified_by = $order->client_id;
                $order->save();
            } else {
                $order = Order::where('id', $data->CustomerReference)->first();
                if ($data->InvoiceStatus == "Paid") {
                    $order->status = "inprogress";
                } else {
                    $order->status = "cancelled";
                }
                $order->modify_by = $order->client_id;
                $order->save();
            }


            //update order transaction status
            $payment_method_status = array('credit', 'credit_card');
            $payment_status = array('unpaid', 'part');
            if ($order_info->reserve == "without") {
                $order_transaction = TableReservationTransaction::where('table_reservation_id', $data->CustomerReference)->whereIn('payment_method', $payment_method_status)->latest()->first();
                $order_transaction->payment_status = ($data->InvoiceStatus == "Paid") ? "part" : "payment_failed";
                $order_transaction->modified_by = $order->client_id;
            } else {
                $order_transaction = OrderTransaction::where('order_id', $data->CustomerReference)->whereIn('payment_method', $payment_method_status)->whereIn('payment_status', $payment_status)->latest()->first();
                $order_transaction->payment_status = ($data->InvoiceStatus == "Paid") ? "paid" : "payment_failed";
                $order_transaction->modify_by = $order->client_id;
            }
            $order_transaction->payment_gateway_reference = $data->InvoiceTransactions[0]->ReferenceId;
            $order_transaction->payment_gateway_date = $data->CreatedDate;
            $order_transaction->payment_gateway_currency = $data->InvoiceTransactions[0]->Currency;
            $order_transaction->payment_gateway_status = $data->InvoiceStatus;
            $order_transaction->payment_gateway_method = $data->InvoiceTransactions[0]->PaymentGateway;
            $order_transaction->paid_at = ($data->InvoiceStatus == "Paid") ? date('Y-m-d H:i:s') : null;
            $order_transaction->save();

            if ($order_info->reserve != "without") {
                //add order tracking
                $order_tracking = new OrderTracking();
                $order_tracking->order_id  = $data->CustomerReference;
                if ($data->InvoiceStatus == "Paid") {
                    $order_tracking->order_status = "in_progress";
                } else {
                    $order_tracking->order_status = "cancelled";
                }
                //$order_tracking->created_by = $user->id;
                $order_tracking->created_by = $order->client_id;
                $order_tracking->time = date('H:i:s');
                $order_tracking->save();
            }

            //$data->InvoiceTransactions[0]->ReferenceId;
            return $data_payment = ['ReferenceId' => $data->InvoiceTransactions[0]->ReferenceId, 'type' => $data->UserDefinedField, 'status' => $data->InvoiceStatus];
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            $response  = ['IsSuccess' => 'false', 'Message' => $exMessage];
        }
        return response()->json($response);
    }

    public function callbackApi(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        try {
            $validator = Validator::make($request->all(), [
                'orderId' => 'required', //order_id or table_reservation_id
                'InvoiceId' => 'required', // Make sure InvoiceId is required
                'type' => 'required' // reserve or order
            ]);

            if ($validator->fails()) {
                return RespondWithBadRequestWithData($validator->errors());
            }

            $mfObj = new MyFatoorahPaymentStatus($this->mfConfig);
            $data = $mfObj->getPaymentStatus($request->InvoiceId, 'invoiceid');

            // Get the order first and check if it exists
            if ($request->type == "order") {
                $order = Order::find($request->orderId);
            } else {
                $order = TableReservation::find($request->orderId);
            }

            if (!$order) {
                return respondError('Order not found', 404);
            }

            $message = $this->getTestMessage($data->InvoiceStatus, $data->InvoiceError);

            // Update order status
            if ($data->InvoiceStatus == "Paid") {
                if ($request->type == "order") {
                    $order->status = "inprogress";
                }
            } else {
                if ($request->type == "order") {
                    $order->status = "cancelled";
                } else {
                    $order->status = "cancel";
                }
            }
            if ($request->type == "order") {
                $order->modify_by = $order->client_id;
            } else {
                $order->modified_by = $order->client_id;
            }
            $order->save();

            // Get the order transaction - handle case where it might not exist
            if ($request->type == "order") {
                $order_transaction = OrderTransaction::where('order_id', $order->id)
                    ->where('payment_method', 'credit')
                    ->where('payment_status', 'unpaid')
                    ->first();
            } else {
                $order_transaction = tableReservationTransaction::where('table_reservation_id', $order->id)
                    ->where('payment_method', 'credit_card')
                    ->first();
            }
            if ($order_transaction) {
                if ($request->type == "order") {
                    $order_transaction->payment_status = ($data->InvoiceStatus == "Paid") ? "paid" : "payment_failed";
                    $order_transaction->modify_by = $order->client_id;
                } else {
                    $order_transaction->payment_status = ($data->InvoiceStatus == "Paid") ? "part" : "payment_failed";
                    $order_transaction->modified_by = $order->client_id;
                }
                $order_transaction->payment_gateway_reference = $data->InvoiceTransactions[0]->ReferenceId ?? null;
                $order_transaction->payment_gateway_date = $data->CreatedDate ?? null;
                $order_transaction->payment_gateway_currency = $data->InvoiceTransactions[0]->Currency ?? null;
                $order_transaction->payment_gateway_status = $data->InvoiceStatus;
                $order_transaction->payment_gateway_method = $data->InvoiceTransactions[0]->PaymentGateway ?? null;
                $order_transaction->paid_at = date('Y-m-d H:i:s');

                $order_transaction->save();
            }

            // Add order tracking
            if ($request->type == "order") {
                $order_tracking = new OrderTracking();
                $order_tracking->order_id = $order->id;
                $order_tracking->order_status = ($data->InvoiceStatus == "Paid") ? "in_progress" : "cancelled";
                $order_tracking->created_by = $order->client_id;
                $order_tracking->time = date('H:i:s');
                $order_tracking->save();
            }
            $data_payment = [
                'status' => $data->InvoiceStatus,
                'order_id' => (int)$request->orderId,
                'type' => $request->type
            ];
            return ResponseWithSuccessData($lang, $data_payment, 1);
        } catch (Exception $e) {
            return respondError('An error occurred.', 400, ['error' => $e->getMessage()]);
        }
    }
    // public function callbackApi(Request $request) {
    //         $lang = $request->header('lang', 'ar');
    //         App::setLocale($lang);

    //         try {
    //             $validator = Validator::make($request->all(), [
    //                 'orderId' => 'required|exists:orders,id', // Optional but must exist in the 'coupons' table
    //             ]);

    //             if ($validator->fails()) {
    //                 return RespondWithBadRequestWithData($validator->errors());
    //             }

    //             $mfObj = new MyFatoorahPaymentStatus($this->mfConfig);
    //             $data  = $mfObj->getPaymentStatus($request->InvoiceId, 'invoiceid');

    //             $message = $this->getTestMessage($data->InvoiceStatus, $data->InvoiceError);

    //             $user = Auth::guard('client')->user();
    //             //update order status
    //             $order = Order::where('id', $data->CustomerReference)->first();
    //             if($data->InvoiceStatus == "Paid"){
    //                 $order->status = "inprogress";
    //             }else{
    //                 $order->status = "cancelled";
    //             }
    //             $order->modify_by = $order->client_id;
    //             $order->save();

    //             //update order transaction status
    //             $order_transaction = OrderTransaction::where('order_id', $data->CustomerReference)->where('payment_method', 'credit_card')->first();
    //             $order_transaction->payment_status = ($data->InvoiceStatus == "Paid") ? "paid" : "unpaid";
    //             $order_transaction->payment_gateway_reference = $data->InvoiceTransactions[0]->ReferenceId;
    //             $order_transaction->payment_gateway_date = $data->CreatedDate;
    //             $order_transaction->payment_gateway_currency = $data->InvoiceTransactions[0]->Currency;
    //             $order_transaction->payment_gateway_status = $data->InvoiceStatus;
    //             $order_transaction->payment_gateway_method = $data->InvoiceTransactions[0]->PaymentGateway;
    //             $order_transaction->modify_by = $order->client_id;
    //             $order_transaction->save();

    //             //add order tracking
    //             $order_tracking = new OrderTracking();
    //             $order_tracking->order_id  = $data->CustomerReference;
    //             if($data->InvoiceStatus == "Paid"){
    //                 $order_tracking->order_status = "in_progress";
    //             }else{
    //                 $order_tracking->order_status = "cancelled";
    //             }
    //             //$order_tracking->created_by = $user->id;
    //             $order_tracking->created_by = $order->client_id;
    //             $order_tracking->time = date('H:i:s');
    //             $order_tracking->save();

    //             //$data->InvoiceTransactions[0]->ReferenceId;
    //             $data_payment = ['status'=>$data->InvoiceStatus, 'order_id'=>(int)$request->orderId];
    //             return ResponseWithSuccessData($lang, $data_payment, 1);

    //         } catch (Exception $e) {
    //             //return RespondWithBadRequest($lang, 34);
    //             return respondError('An error occurred.', 400, ['error' => $e->getMessage()]);
    //         }
    //     }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Example on how to Display the enabled gateways at your MyFatoorah account to be displayed on the checkout page
     * Provide the checkout method with the order id to display its total amount and currency
     *
     * @return View
     */
    public function checkout()
    {
        try {
            //You can get the data using the order object in your system
            $orderId = request('oid') ?: 147;
            $order   = $this->getTestOrderData($orderId);

            //You can replace this variable with customer Id in your system
            $customerId = request('customerId');

            //You can use the user defined field if you want to save card
            $userDefinedField = config('myfatoorah.save_card') && $customerId ? "CK-$customerId" : '';

            //Get the enabled gateways at your MyFatoorah acount to be displayed on checkout page
            $mfObj          = new MyFatoorahPaymentEmbedded($this->mfConfig);
            $paymentMethods = $mfObj->getCheckoutGateways($order['total'], $order['currency'], config('myfatoorah.register_apple_pay'));

            if (empty($paymentMethods['all'])) {
                throw new Exception('noPaymentGateways');
            }

            //Generate MyFatoorah session for embedded payment
            $mfSession = $mfObj->getEmbeddedSession($userDefinedField);

            //Get Environment url
            $isTest = $this->mfConfig['isTest'];
            $vcCode = $this->mfConfig['countryCode'];

            $countries = MyFatoorah::getMFCountries();
            $jsDomain  = ($isTest) ? $countries[$vcCode]['testPortal'] : $countries[$vcCode]['portal'];

            return view('website.myfatoorah.checkout', compact('mfSession', 'paymentMethods', 'jsDomain', 'userDefinedField'));
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            return view('website.myfatoorah.error', compact('exMessage'));
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Example on how the webhook is working when MyFatoorah try to notify your system about any transaction status update
     */
    public function webhook(Request $request)
    {
        try {
            //Validate webhook_secret_key
            $secretKey = config('myfatoorah.webhook_secret_key');
            if (empty($secretKey)) {
                return response(null, 404);
            }

            //Validate MyFatoorah-Signature
            $mfSignature = $request->header('MyFatoorah-Signature');
            if (empty($mfSignature)) {
                return response(null, 404);
            }

            //Validate input
            $body  = $request->getContent();
            $input = json_decode($body, true);
            if (empty($input['Data']) || empty($input['EventType']) || $input['EventType'] != 1) {
                return response(null, 404);
            }

            //Validate Signature
            if (!MyFatoorah::isSignatureValid($input['Data'], $secretKey, $mfSignature, $input['EventType'])) {
                return response(null, 404);
            }

            //Update Transaction status on your system
            $result = $this->changeTransactionStatus($input['Data']);

            return response()->json($result);
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            return response()->json(['IsSuccess' => false, 'Message' => $exMessage]);
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    private function changeTransactionStatus($inputData)
    {
        //1. Check if orderId is valid on your system.
        $orderId = $inputData['CustomerReference'];

        //2. Get MyFatoorah invoice id
        $invoiceId = $inputData['InvoiceId'];

        //3. Check order status at MyFatoorah side
        if ($inputData['TransactionStatus'] == 'SUCCESS') {
            $status = 'Paid';
            $error  = '';
        } else {
            $mfObj = new MyFatoorahPaymentStatus($this->mfConfig);
            $data  = $mfObj->getPaymentStatus($invoiceId, 'InvoiceId');

            $status = $data->InvoiceStatus;
            $error  = $data->InvoiceError;
        }

        $message = $this->getTestMessage($status, $error);

        //4. Update order transaction status on your system
        return ['IsSuccess' => true, 'Message' => $message, 'Data' => $inputData];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    private function getTestOrderData($orderId, $reserve)
    {
        try {
            if ($reserve == "with") {
                $order_details = Order::where('id', $orderId)->first();
                if (!$order_details) {
                    $message = "Order is not valid.";
                    return redirect()->back()->withErrors($message)->withInput();
                }

                $customer_name = ($order_details->Client) ? $order_details->Client->name : "Customer name";
                $customer_email = ($order_details->Client) ? $order_details->Client->email : "customer@email.com";
                $mobile_country_code = ($order_details->Client) ? $order_details->Client->country_code : "+020";
                $customer_mobile = ($order_details->Client) ? $order_details->Client->phone : "01010101010";
                $total_price = 0;

                $order_transaction_details = $order_details->orderTransactions()->latest()->first();
                // if($order_details->type != "Delivery"){
                //     if($order_transaction_details->payment_status == "part"){
                //         $total_price = $order_details->total_price_after_tax;
                //     }else{
                //         $total_price = $order_details->total_price_after_tax;
                //     }
                // }else{
                //     $total_price = $order_details->total_price_after_tax;
                // }
                $total_price = $order_transaction_details->paid;

                return [
                    'total'               => $total_price,
                    'currency'            => 'EGP',
                    'customer_name'       => $customer_name,
                    'customer_email'      => $customer_email,
                    'mobile_country_code' => $mobile_country_code,
                    'customer_mobile'     => $customer_mobile,
                    'lang'                => "en",
                ];
            } else {
                $order_details = TableReservation::where('id', $orderId)->first();
                if (!$order_details) {
                    $message = "Order is not valid.";
                    return redirect()->back()->withErrors($message)->withInput();
                }

                $customer_name = ($order_details->client) ? $order_details->client->name : "Customer name";
                $customer_email = ($order_details->client) ? $order_details->client->email : "customer@email.com";
                $mobile_country_code = ($order_details->client) ? $order_details->client->country_code : "+020";
                $customer_mobile = ($order_details->client) ? $order_details->client->phone : "01010101010";
                $total_price = 0;

                $order_transaction_details = $order_details->transaction()->latest()->first();
                // if($order_details->type != "Delivery"){
                //     if($order_transaction_details->payment_status == "part"){
                //         $total_price = $order_details->total_price_after_tax;
                //     }else{
                //         $total_price = $order_details->total_price_after_tax;
                //     }
                // }else{
                //     $total_price = $order_details->total_price_after_tax;
                // }
                $total_price = $order_transaction_details->paid;

                return [
                    'total'               => $total_price,
                    'currency'            => 'EGP',
                    'customer_name'       => $customer_name,
                    'customer_email'      => $customer_email,
                    'mobile_country_code' => $mobile_country_code,
                    'customer_mobile'     => $customer_mobile,
                    'lang'                => "en",
                ];
            }
        } catch (\Exception $e) {
            $message = "Error checking your order.";
            return redirect()->back()->withErrors($message)->withInput();
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    private function getTestMessage($status, $error)
    {
        if ($status == 'Paid') {
            return 'Invoice is paid.';
        } else if ($status == 'Failed') {
            return 'Invoice is not paid due to ' . $error;
        } else if ($status == 'Expired') {
            return $error;
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
