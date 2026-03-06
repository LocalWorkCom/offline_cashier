<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;
use App\Models\Order;
use App\Models\OrderTransaction;
use App\Models\OrderTracking;
use MyFatoorah\Library\MyFatoorah;
use MyFatoorah\Library\API\Payment\MyFatoorahPayment;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentEmbedded;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentStatus;
use Exception;
use Session;
use Illuminate\Support\Facades\Auth;
use App\Services\MyFatoorahService;

class MyFatoorahController extends Controller {

    protected $myFatoorahService;
    public $mfConfig = [];

    public function __construct(MyFatoorahService $myFatoorahService)
    {
        $this->myFatoorahService = $myFatoorahService;
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
    public function index($orderId, $reserve) {
        try {
            $type = "web";
            $payment_link = $this->myFatoorahService->index($orderId, $type, $reserve);
            if($type === "web")
            {
                return redirect($payment_link['link']);
            }else{
                return $payment_link;
            }
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
    private function getPayLoadData($orderId = null) {
        // $callbackURL = route('myfatoorah.callback');

        // //You can get the data using the order object in your system
        // $order = $this->getTestOrderData($orderId);

        // return [
        //     'NotificationOption' => 'All',
        //     'CustomerName'       => $order['customer_name'],
        //     'InvoiceValue'       => $order['total'],
        //     'DisplayCurrencyIso' => $order['currency'],
        //     'CustomerEmail'      => $order['customer_email'],
        //     'CallBackUrl'        => $callbackURL,
        //     'ErrorUrl'           => $callbackURL,
        //     'MobileCountryCode'  => $order['mobile_country_code'],
        //     'CustomerMobile'     => $order['customer_mobile'],
        //     'Language'           => $order['lang'],
        //     'CustomerReference'  => $orderId,
        //     'UserDefinedField'   => $type,
        //     'SourceInfo'         => 'Laravel ' . app()::VERSION . ' - MyFatoorah Package ' . MYFATOORAH_LARAVEL_PACKAGE_VERSION
        // ];
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get MyFatoorah Payment Information
     * Provide the callback method with the paymentId
     * 
     * @return Response
     */
    public function callback() {
        try {
            $paymentId = request('paymentId');
            $payment_responce = $this->myFatoorahService->callback($paymentId);
            $data = json_decode($payment_responce['type'], true);
            if($data['type'] === "web"){
                return redirect()->route('payment-transaction', [$payment_responce['ReferenceId'], $data['reserve']]);
            }else{
                return $parment = array('status' => $payment_responce['status']);
            }
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            $response  = ['IsSuccess' => 'false', 'Message' => $exMessage];
        }
        return response()->json($response);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Example on how to Display the enabled gateways at your MyFatoorah account to be displayed on the checkout page
     * Provide the checkout method with the order id to display its total amount and currency
     * 
     * @return View
     */
    public function checkout() {
        try {
            //You can get the data using the order object in your system
            // $orderId = request('oid') ?: 147;
            // $order   = $this->getTestOrderData($orderId);

            // //You can replace this variable with customer Id in your system
            // $customerId = request('customerId');

            // //You can use the user defined field if you want to save card
            // $userDefinedField = config('myfatoorah.save_card') && $customerId ? "CK-$customerId" : '';

            // //Get the enabled gateways at your MyFatoorah acount to be displayed on checkout page
            // $mfObj          = new MyFatoorahPaymentEmbedded($this->mfConfig);
            // $paymentMethods = $mfObj->getCheckoutGateways($order['total'], $order['currency'], config('myfatoorah.register_apple_pay'));

            // if (empty($paymentMethods['all'])) {
            //     throw new Exception('noPaymentGateways');
            // }

            // //Generate MyFatoorah session for embedded payment
            // $mfSession = $mfObj->getEmbeddedSession($userDefinedField);

            // //Get Environment url
            // $isTest = $this->mfConfig['isTest'];
            // $vcCode = $this->mfConfig['countryCode'];

            // $countries = MyFatoorah::getMFCountries();
            // $jsDomain  = ($isTest) ? $countries[$vcCode]['testPortal'] : $countries[$vcCode]['portal'];

            // return view('website.myfatoorah.checkout', compact('mfSession', 'paymentMethods', 'jsDomain', 'userDefinedField'));
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            return view('website.myfatoorah.error', compact('exMessage'));
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Example on how the webhook is working when MyFatoorah try to notify your system about any transaction status update
     */
    public function webhook(Request $request) {
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
    private function changeTransactionStatus($inputData) {
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
    private function getTestOrderData($orderId) {
        try{
            $order_details = Order::where('id', $orderId)->first();
        if (!$order_details) {
            $message = "Order is not valid.";
            return redirect()->back()->withErrors($message)->withInput();
        }

        $customer_name = ($order_details->Client) ? $order_details->Client->name : "Customer name";
        $customer_email = ($order_details->Client) ? $order_details->Client->email : "customer@email.com";
        $mobile_country_code = ($order_details->Client) ? $order_details->Client->country_code : "+020";
        $customer_mobile = ($order_details->Client) ? $order_details->Client->phone : "01010101010";

        return [
            'total'               => $order_details->total_price_after_tax,
            'currency'            => 'EGP',
            'customer_name'       => $customer_name,
            'customer_email'      => $customer_email,
            'mobile_country_code' => $mobile_country_code,
            'customer_mobile'     => $customer_mobile,
            'lang'                => "en",
        ];

        }catch (\Exception $e) {
            $message = "Error checking your order.";
            return redirect()->back()->withErrors($message)->withInput();
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    private function getTestMessage($status, $error) {
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
