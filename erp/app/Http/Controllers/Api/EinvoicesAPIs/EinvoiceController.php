<?php

namespace App\Http\Controllers\Api\EinvoicesAPIs;

use App\Http\Controllers\Controller;
use App\Models\Einvoice;
use App\Models\Order;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class EinvoiceController extends Controller
{

    function invoiceApi($invoiceId)
    {
        $InvData = [];

        // foreach ($invoiceIds as $invoiceId) {
        $invoice = get_by_md5_id($invoiceId, 'orders');
        if (!$invoice) {
            return json_encode([
                'success' => 0,
                'Message' => 'Invoice id not valid'
            ]);
        }
        // $payment = PaymentModel::find($invoice->payment_id); // get payment
        // $payment->amount = str_replace(',', '', convert_currency($payment->amount, 'SAR', 'EGP'));


        // if ($payment && isset($payment->prev_package) && $payment->prev_package > 0) {
        // if ($paid) {

        //     $ResponseJson = $this->createCreditNoteJson($invoice->id);
        //     if ($ResponseJson['success']) {
        //         $CreditNot = $ResponseJson['creditNot'];
        //         $creditnot_id =  $ResponseJson['invoice_id'];
        //     }
        // }

        // }
        $reciever = User::where('users.id', $invoice->client_id)->join('countries', 'countries.id', 'users.country_id')
            ->select('users.*', 'countries.name_ar as country_name')->first(); // get receiver with his country of einvoice data
        if (!$reciever) {
            return json_encode(['success' => 0, 'message' => trans('admin.reciever-exists')]);
        }

        // $package = PackageModel::find($payment->package); // get package of user invoice
        // $client = new Client(); // for client http to calling api
        // $access_token = loginTaxes();
        // get issuer address
        // if ($invoice->invoice_type != 'c') {

        $issureAddress = [
            'branchId' => "0",
            "country" => "EG",
            "governate" => "Cairo",
            "regionCity" => "",
            "street" =>  "",
            "buildingNumber" => ""
        ];
        // get receiver address
        $recieverAddress = [
            //'branchId' => "0",
            "country" => "EG",
            "governate" => "Cairo",
            "regionCity" => "",
            "street" => "",
            "buildingNumber" => ""
        ];
        $issuer = [
            'address' => $issureAddress,
            "type" => "B",
            "id" => einvoice_settings('tax_issuer_id'),
            "name" => einvoice_settings('tax_name')
        ];
        $reciever_data = [
            "address" => $recieverAddress,
            "type" => "P",
            "id" => "",
            "name" => $reciever->name
        ];


        $checkDiscount = $invoice->discount_id;

        $totalDiscount =  calcDiscount($invoice->total_price_after_tax, $checkDiscount);
        //calculate discount of einvoice


        //percentage of taxes
        //calculate taxes
        $taxesRate = getBranchSettings($invoice->branch_id, 'tax_percentage'); // settings
        $taxes = $invoice->tax_value;
        if ($taxes) {
            $taxType = "T1";
            $subType = "V009";
        } else {
            $taxType = '';
            $subType = '';
        }
        //calculate amount without taxes
        $amount = $invoice->total_price_befor_tax;



        //prepare body of api
        $data = [

            "issuer" => $issuer,
            "receiver" => $reciever_data,
            "documentType" => "I",
            "documentTypeVersion" => (string)einvoice_settings('tax_invoice_version'),
            "dateTimeIssued" => date("Y-m-d\TH:i:s\Z"),
            "taxpayerActivityCode" => einvoice_settings('tax_activity'),
            "internalID" => $invoice->serial,
            "invoiceLines" => [
                [
                    "description" => "subscription",
                    "itemType" => einvoice_settings('tax_item_code_type'),
                    "itemCode" => einvoice_settings('tax_item_code'),
                    "unitType" => "MON",
                    "quantity" => 1,
                    "salesTotal" => (float)round($amount, 5),
                    "total" => (float)round($amount + $taxes, 5),
                    "valueDifference" => 0.00000,
                    "totalTaxableFees" => 0.00000,
                    "netTotal" => (float)round($amount, 5),
                    "itemsDiscount" => (float)round($totalDiscount, 5),
                    "unitValue" => [
                        "currencySold" => "EGP",
                        "amountEGP" => (float)round($amount, 5),
                    ],
                    "discount" => [
                        "rate" => $checkDiscount->value ?? 0,
                        "amount" => (float)round($totalDiscount, 5),
                    ],
                    "taxableItems" => ($taxes) ?
                        [
                            [
                                "taxType" => $taxType,
                                "amount" => (float)round($taxes, 5),
                                "subType" => $subType,
                                "rate" => $taxesRate
                            ]
                        ]
                        : null
                ]
            ],
            "totalDiscountAmount" => (float)round($totalDiscount, 5),
            "totalSalesAmount" => (float)round($amount, 5),
            "netAmount" =>  (float)round($amount, 5),
            "taxTotals" => ($taxes) ? [
                [
                    "taxType" => $taxType,
                    "amount" => (float)round($taxes, 5),
                ]
            ] : null,
            "totalAmount" => (float)round($amount + $taxes, 5),
            "extraDiscountAmount" => 0.00000,
            "totalItemsDiscountAmount" => (float)round($totalDiscount, 5),

        ];
        $InvData['documents'][] = $data;
        // }
        // if ($CreditNot != '') {
        //     $InvData['documents'][] = $CreditNot;
        // }
        // }
        $response_array = array(
            'success' => 1,
            'documentJson' => $InvData,
            // 'credit_not_id' => ($creditnot_id) ? md5($creditnot_id) : 0

        );
        $Response = Response::json($response_array, 200);
        return $Response;
    }
    function submitWithSignature($signatures, $invoiceId)
    {
        $signatures = $_POST['signature'];

        $invoiceId = $_POST['invoiceId'];

        $submissionDetails = array();

        $subscriptionIds = array();


        $invoice = get_by_md5_id($invoiceId, 'orders');
        if (!$invoice) {
            return json_encode([
                'Success' => false,
                'Message' => 'Invoice id = ' . $invoiceId . ' is not valid'
            ]);
        }

        $client = new Client(); // for client http to calling api
        $response_token = $this->loginTaxes();
        if (isset($response_token['access_token'])) {


            $access_token = $response_token['access_token'];

            if (einvoice_settings('tax_invoice_version') == "1.0") {
                $urlappend = '/api/v1/documentsubmissions';
                // $signature = json_decode($signatures, true);
            } else {
                $urlappend = '/api/v0.9/documentsubmissions';
            }
            // $InvData = $signatures;

            // get url if preprod or prod
            if (einvoice_settings('tax_live') == 0) {
                $url = einvoice_settings('apiBaseUrlPreprodEg');
            } else {
                $url = einvoice_settings('apiBaseUrlProdEg');
            }
            // file_put_contents('uploads/eg_tax_invoice/invoice.json', json_encode($InvData));

            try {

                // if einvoice submmited successfully
                ini_set('serialize_precision', -1);
                $response = $client->request('POST', $url . $urlappend, [
                    'headers'  =>  [
                        'Authorization' => "Bearer {$access_token}",
                        'content-type' => 'application/json',
                        'accept' => 'application/ld+json'
                    ],
                    'body' => $signatures

                ]);
                $response = json_decode($response->getBody()->getContents(), true);
                // dd($response);
                if ($response['submissionId']) {

                    foreach ($response['acceptedDocuments'] as $acceptDocument) {

                        $submissionDetails['uuid'][] = $acceptDocument['uuid'];
                        $submissionDetails['error_msg'][] = trans('admin.submit-success');
                    }
                } else {
                    $submissionDetails['uuid'][] = '';
                    $submissionDetails['error_msg'][] = trans('admin.invalid-data-invoice');
                }

                if (sizeof($submissionDetails) > 0) {
                    for ($i = 0; $i < sizeof($subscriptionIds); $i++) {
                        helper_update_by_id(
                            [
                                'uuid' =>    $submissionDetails['uuid'][$i],
                                'error_msg' =>    $submissionDetails['error_msg'][$i],
                            ],
                            $subscriptionIds[$i],
                            'einvoices'
                        );
                    }
                }

                $uuids = $submissionDetails['uuid'];
            } catch (GuzzleException $exception) {
                // if response is invaild
                $response = json_decode($exception->getResponse()->getBody(true)->getContents(), true);
                if ($response) {
                    $errors = $response['error']; //get error message why response invalid

                    return json_encode(['success' => 0, 'message' => $errors]);
                } else {
                    return json_encode(['success' => 0, 'message' => 'Unknown Error']);
                }
                // $invoice->error_msg = $errors;
                // $invoice->save();
            }
            // }
            return json_encode(['success' => 1,  'message' => 'Successfully Submitted', 'uuid' => $uuids]);
        }
        return json_encode(['success' => 0, 'message' => trans('admin.portal-error')]);
    }
    /**
     *
     * submit einvoice to EGYPTIAN TAX  Without Sign
     *
     * @param int subscription invoice id
     * @return json api response
     *
     */


    function invoiceSubmission($invoiceId)
    {
        // $invoice = get_by_md5_id($invoiceId, 'orders');
        // if (!$invoice) {
        //     return json_encode([
        //         'success' => 0,
        //         'message' => 'Invoice id invalid'
        //     ]);
        // }
        $invoice = Order::find($invoiceId);
        // $invoice = SubscriptionInvoicesModel::where('orders.id', $invoiceId)->first();
        // $invoice=get_invoice_details($invoiceId);
        // $payment = PaymentModel::find($invoice->payment_id); // get payment
        // $payment->amount = str_replace(',', '', convert_currency($payment->amount, 'SAR', 'EGP'));


        $reciever = User::where('users.id', $invoice->client_id)->join('countries', 'countries.id', 'users.country_id')
            ->select('users.*', 'countries.name_ar as country_name')->first(); // get receiver with his country of einvoice data
        // $package = PackageModel::find($payment->package); // get package of user invoice
        $client = new Client(); // for client http to calling api
        $response_token = $this->loginTaxes();
        if (!$reciever) {
            return json_encode(['success' => 0, 'message' => trans('admin.reciever-exists')]);
        }

        if (isset($response_token['access_token'])) {

            $access_token = $response_token['access_token'];

            // get issuer address
            $issureAddress = [
                "branchId" => "0",
                "country" => "EG",
                "governate" => "Cairo",
                "regionCity" => "fddfdf",
                "street" => "ddss",
                "buildingNumber" => "2"
            ];


            // get receiver address
            $recieverAddress = [
                "country" => "EG",
                "governate" => "Cairo",
                "regionCity" => "",
                "street" => "",
            ];

            //company
            $issuer = [
                'address' => $issureAddress,
                "type" => "B",
                "id" => einvoice_settings('tax_issuer_id'), // السجل التجاري
                "name" => "ahmed" // اسم الشركه
            ];
            //user
            $reciever_data = [
                "address" => $recieverAddress,
                "type" => "P",
                "id" => "",
                "name" => $reciever->name
            ];
            // get url if preprod or prod
            if (einvoice_settings('tax_live') == 0) {
                $url = einvoice_settings('apiBaseUrlPreprodEg');
            } else {
                $url = einvoice_settings('apiBaseUrlProdEg');
            }

            // $checkDiscount = $invoice->discount_id;

            // $totalDiscount =  calcDiscount($invoice->total_price_after_tax, $checkDiscount);


            //percentage of taxes
            //calculate taxes
            $taxesRate = 14; // settings
            $taxes = $invoice->tax_value;
            if ($taxes) {
                $taxType = "T1";
                $subType = "V009";
            } else {
                $taxType = '';
                $subType = '';
            }
            //calculate amount without taxes
            $amount = $invoice->total_price_befor_tax;
            //prepare body of api
            $data = [
                // "documents"=> [
                [
                    "issuer" => $issuer,
                    "receiver" => $reciever_data,
                    "documentType" => "I",
                    "documentTypeVersion" => "1.0",
                    "dateTimeIssued" => gmdate("Y-m-d\TH:i:s\Z", strtotime("-1 hour")),
                    "taxpayerActivityCode" => einvoice_settings('tax_activity'),
                    "internalID" => "Inv-2020-01",
                    "invoiceLines" => [
                        [
                            "description" => "subscription",
                            "itemType" => einvoice_settings('tax_item_code_type'),
                            "itemCode" => "EG-675170532-1",
                            "unitType" => "MON",
                            "quantity" => 1,
                            "salesTotal" => (float)round($amount, 5),
                            "total" =>  (float)round($amount + $taxes, 5),
                            "valueDifference" => 0.00000,
                            "totalTaxableFees" => 0.00000,
                            "netTotal" => (float)round($amount, 5,),
                            "itemsDiscount" => 0,
                            "unitValue" => [
                                "currencySold" => "EGP",
                                "amountEGP" => (float)round($amount, 5),
                            ],
                            "discount" => [
                                "rate" => $checkDiscount->value ?? 0,
                                "amount" =>   0,
                            ],
                            "taxableItems" => [
                                [
                                    "taxType" => $taxType,
                                    "amount" =>  (float)round($taxes, 5),
                                    "subType" => $subType,
                                    "rate" => $taxesRate
                                ]
                            ]
                        ]
                    ],
                    "totalDiscountAmount" => 0,
                    "totalSalesAmount" => (float)round($amount, 5),
                    "netAmount" => (float)round($amount, 5),
                    "taxTotals" => [
                        [
                            "taxType" => $taxType,
                            "amount" => (float)round($taxes, 5)
                        ]
                    ],
                    "totalAmount" => (float)round($amount + $taxes, 5),
                    "extraDiscountAmount" => 0.00000,
                    "totalItemsDiscountAmount" => 0,
                ]
                // ]
            ];
            $signature = "fdsfdsdsf";
            // $data['documents'][0]['signatures'][] = ['signatureType' => "I", 'value' => $signature];
            // if (einvoice_settings('tax_invoice_version') == "1.0") {
            $urlappend = '/api/v1/documentsubmissions';
            // $signature = signature($data[0]);
            $data[0]['signatures'][] = ['signatureType' => "I", 'value' => $signature];
            // } else {
            //     dd(0);
            //     $urlappend = '/api/v0.9/documentsubmissions';
            // }
            $InvData['documents'] = $data;
            // file_put_contents('uploads/eg_tax_invoice/invoice.json', json_encode($InvData));
            try {


                // if einvoice submmited successfully
                ini_set('serialize_precision', -1);
                $response = $client->request('POST', $url . $urlappend, [
                    'headers'  =>  [
                        'Authorization' => "Bearer {$access_token}",
                        'content-type' => 'application/json',
                        'accept' => 'application/ld+json'
                    ],
                    'body' => json_encode($InvData)

                ]);
                $response = json_decode($response->getBody()->getContents(), true);
                if ($response['submissionId']) {

                    $submissionDetails['uuid'] = $response['acceptedDocuments'][0]['uuid'];

                    $submissionDetails['error_msg'] = trans('admin.submit-success');
                } else {

                    $submissionDetails['uuid'] = '';
                    $submissionDetails['error_msg'] = trans('admin.invalid-data-invoice');
                }

                helper_update_by_id($submissionDetails, $invoice->id, 'orders');

                // update submittion id
                return json_encode(['success' => 1,  'message' => $submissionDetails['error_msg'], 'uuid' => $submissionDetails['uuid']]);
            } catch (GuzzleException $exception) {
                // if response is invaild
                $response = json_decode($exception->getResponse()->getBody(true)->getContents(), true);
                // dd($invoice);
                if ($response) {
                    $errors = $response['error'];
                    helper_update_by_id(['error_msg' => $errors], $invoice->id, 'orders');
                    return json_encode(['success' => 0, 'message' => $errors]);
                } else {
                    return json_encode(['success' => 0, 'message' => 'Unknown Error']);
                }
            }
        }
        return json_encode(['success' => 0, 'message' => trans('admin.portal-error')]);
    }
    function DocumentDetails($invoiceIds)
    {
        if (!is_array($invoiceIds) && $invoiceIds != 0) {
            $id = $invoiceIds;
            $invoiceIds = array();
            array_push($invoiceIds, $id);
        }
        // get access token from login
        $response_token = $this->loginTaxes();
        if (isset($response_token['access_token'])) {

            $access_token = $response_token['access_token'];

            $client = new Client(); // for client http to calling api

            $result = array();
            if (einvoice_settings('tax_live') == 0) {
                $url = einvoice_settings('apiBaseUrlPreprodEg');
            } else {
                $url = einvoice_settings('apiBaseUrlProdEg');
            }
            foreach ($invoiceIds as $invoiceId) {

                $invoice = get_by_md5_id($invoiceId, 'orders');
                if (!$invoice) {
                    return json_encode([
                        'success' => 0,
                        'message' => 'Invoice id invalid'
                    ]);
                }

                // dd($invoice);

                $appendUrl = '/api/v1.0/documents/' . $invoice->uuid . '/details';
                try {

                    $response = $client->request('GET', $url . $appendUrl, [
                        'headers'  =>  [
                            'Authorization' => "Bearer {$access_token}",
                            'content-type' => 'application/json',
                            'accept' => 'application/ld+json'
                        ]
                    ]);
                    $response = json_decode($response->getBody()->getContents(), true);
                    $res['tax_status'] = $response['status'];
                    $res['validation_ar'] = $res['validation_en'] = "";
                    if ($response['validationResults']) {


                        if ($response['validationResults']['status'] == 'Invalid') {
                            foreach ($response['validationResults']['validationSteps'] as $validate_error) {
                                // foreach ($validate_error as $error) {
                                // dd($validate_error['status']);
                                if ($validate_error['status'] != 'Valid') {
                                    // dd($validate_error);
                                    // foreach ($validate_error['error'] as $error) {

                                    if ($validate_error['error']['error']) {
                                        $res['validation_en'] .= $validate_error['error']['error'] . ',';
                                    }
                                    if ($validate_error['error']['errorAr']) {
                                        $res['validation_ar'] .= $validate_error['error']['errorAr'] . ',';
                                    }
                                }
                                // }
                            }
                        }
                    }

                    $res['publicUrl'] = $response['publicUrl'];
                    if ($response['cancelRequestDate']) {
                        $res['cancelRequestDate'] = $response['cancelRequestDate'];
                    }
                    if ($response['rejectRequestDate']) {
                        $res['rejectRequestDate'] = $response['rejectRequestDate'];
                    }

                    $res['submissionDate'] = $response['dateTimeRecevied'];

                    helper_update_by_id($res, $invoice->id, 'orders');
                    array_push($result, $res);
                    // dd($res);
                } catch (GuzzleException $exception) {
                    // if response is invaild
                    $response = json_decode($exception->getResponse()->getBody(true)->getContents(), true);
                    if ($response) {

                        $errors = $response['error']; //get error message why response invalid
                        return json_encode(['success' => 0, 'message' => $errors]);
                    } else {
                        return json_encode(['success' => 0, 'message' => 'Unknown Error']);
                    }
                }
            }
            return json_encode(['success' => 1, 'result' => $result]);
        }
        return json_encode(['success' => 0, 'message' => trans('admin.portal-error')]);
    }


    function ChangeDocumentStatus($uuid, $status, $reason)
    {
        $response_token = $this->loginTaxes();
        if (isset($response_token['access_token'])) {


            $access_token = $response_token['access_token'];

            $client = new Client(); // for client http to calling api

            if (einvoice_settings('tax_live ') == 0) {
                $url = einvoice_settings('apiBaseUrlPreprodEg');
            } else {
                $url = einvoice_settings('apiBaseUrlProdEg');
            }
            $appendUrl = '/api/v1.0/documents/state/' . $uuid . '/state';
            try {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$access_token}",
                    'content-type' => 'application/json',
                    'accept' => 'application/ld+json'
                ])->put($url . $appendUrl, [
                    "status" => $status,
                    "reason" => $reason
                ]);

                $response = json_decode($response->getBody()->getContents(), true);
            } catch (GuzzleException $exception) {
                // if response is invaild
                $response = json_decode($exception->getResponse()->getBody(true)->getContents(), true);
                // $invoice->error_msg = $errors;
                // $invoice->save();
                return json_encode(['success' => 0, 'message' => 'error']);
            }
        }
        return json_encode(['success' => 0, 'message' => trans('admin.portal-error')]);
    }


    /**
     *
     * submit einvoice to EGYPTIAN TAX
     *
     * @param int subscription invoice id
     * @return json api response
     *
     */

    //test 
    // function createCreditNoteJson($invoiceId)
    // {
    //     $invoice = SubscriptionInvoicesModel::where('orders.id', $invoiceId)->first();

    //     if ($invoice) {
    //         // $invoice=get_invoice_details($invoiceId);
    //         // $payment = PaymentModel::find($invoice->payment_id); // get payment
    //         $checkCreditNot = SubscriptionInvoicesModel::where('orders.payment_id', $invoice->payment_id)
    //             ->where("invoice_type", "c")->first();

    //         if (!$checkCreditNot) {

    //             $CreditNot = SubscriptionInvoicesModel::create([
    //                 "payment_id" => $invoice->payment_id,
    //                 "created_at" => date('Y-m-d'),
    //                 "expire_on" => $invoice->expire_on,
    //                 "invoice_type" => "c",
    //                 "reference_invoice" => $invoice->id,
    //             ]);
    //             $CreditNot->serial = 'CN-' . date("Y") . "-" . $CreditNot->id;
    //             $CreditNot->save();
    //             $serial = $CreditNot->serial;
    //             $id = $CreditNot->id;
    //         } else {
    //             $serial = $checkCreditNot->serial;
    //             $id =   $checkCreditNot->id;
    //         }
    //         $payment->amount = str_replace(',', '', convert_currency($payment->amount, 'SAR', 'EGP'));
    //         $reciever = UsersModel::where('users.id', $payment->user_id)->join('country', 'country.id', 'users.country')
    //             ->select('users.*', 'country.name as country_name')->first(); // get receiver with his country of einvoice data
    //         if (!$reciever) {
    //             return json_encode(['success' => 0, 'message' => 'reciever for this credit dosen\'t exist']);
    //         }
    //         // get issuer address
    //         $issureAddress = [
    //             'branchId' => "0",
    //             "country" => "EG",
    //             "governate" => "Cairo",
    //             "regionCity" => ($settings->city) ? $settings->city : "",
    //             "street" => ($settings->street) ? $settings->street : "",
    //             "buildingNumber" => ($settings->building_number) ? $settings->building_number : ""
    //         ];
    //         // get receiver address
    //         $recieverAddress = [
    //             'branchId' => "0",
    //             "country" => "EG",
    //             "governate" => "Cairo",
    //             "regionCity" => ($reciever->city) ? $reciever->city : "",
    //             "street" => ($reciever->address) ? $reciever->address : "",
    //             "buildingNumber" => ''
    //         ];
    //         $issuer = [
    //             'address' => $issureAddress,
    //             "type" => "B",
    //             "id" => $settings->tax_issuer_id,
    //             "name" => $settings->tax_name
    //         ];
    //         $reciever_data = [
    //             "address" => $recieverAddress,
    //             "type" => "P",
    //             "id" => "",
    //             "name" => $reciever->name
    //         ];


    //         //calculate discount of einvoice
    //         // if($invoice->billing_type=='monthly')
    //         // {
    //         //     $discountRate=$package->dis_month; //percentage monthly package discount
    //         //     $totalDiscount=$invoice->amount*($discountRate/100); //calculate monthly package discount
    //         // }
    //         // elseif($invoice->billing_type=='yearly')
    //         // {
    //         //     $discountRate=$package->dis_year; //percentage yearly package discount
    //         //     $totalDiscount=$invoice->amount*($discountRate/100); //calculate yearly package discount
    //         // }

    //         if ($reciever->country_name == 'Egypt') //if it local payment
    //         {
    //             //percentage of taxes
    //             $taxesRate = 14;
    //             $taxType = "T1";
    //             $subType = "V009";
    //             //calculate taxes
    //             $taxes = taxesCalc($payment->amount, $taxesRate);
    //             //calculate amount without taxes
    //             $amount = $payment->amount - $taxes;
    //         } else // if it a export invoice
    //         {
    //             $taxesRate =  0.00000;
    //             $taxType = "T1";
    //             $subType = "V001";
    //             $taxes =  0.00000;
    //             $amount = $payment->amount;
    //         }
    //         //prepare body of api
    //         $InvData = [
    //             // "documents"=> [
    //             // [
    //             "issuer" => $issuer,
    //             "receiver" => $reciever_data,
    //             "documentType" => "C",
    //             "documentTypeVersion" => "1.0",
    //             "dateTimeIssued" => date("Y-m-d\TH:i:s\Z"),
    //             "taxpayerActivityCode" => $settings->tax_activity,
    //             "internalID" => $serial,
    //             "invoiceLines" => [
    //                 [
    //                     "description" => "subscription",
    //                     "itemType" => $settings->tax_item_code_type,
    //                     "itemCode" => $settings->tax_item_code,
    //                     "unitType" => "MON",
    //                     "quantity" => 1,
    //                     "salesTotal" =>  (float)round($amount, 5),
    //                     "total" => (float)round($amount + $taxes, 5),
    //                     "valueDifference" => 0.00000,
    //                     "totalTaxableFees" => 0.00000,
    //                     "netTotal" =>  (float)round($amount, 5),
    //                     "itemsDiscount" => 0.00000,
    //                     "unitValue" => [
    //                         "currencySold" => "EGP",
    //                         "amountEGP" =>  (float)round($amount, 5)
    //                     ],
    //                     "discount" => [
    //                         "rate" =>  0.00000,
    //                         "amount" =>  0.00000
    //                     ],
    //                     "taxableItems" => [
    //                         [
    //                             "taxType" => $taxType,
    //                             "amount" => (float)round($taxes, 5),
    //                             "subType" => $subType,
    //                             "rate" => $taxesRate
    //                         ]
    //                     ]
    //                 ]
    //             ],
    //             "totalDiscountAmount" => 0.00000,
    //             "totalSalesAmount" =>  (float)round($amount, 5),
    //             "netAmount" =>  (float)round($amount, 5),
    //             "taxTotals" => [
    //                 [
    //                     "taxType" => $taxType,
    //                     "amount" => (float)round($taxes, 5)
    //                 ]
    //             ],
    //             "totalAmount" => (float)round($amount + $taxes, 5),
    //             "extraDiscountAmount" => 0.00000,
    //             "totalItemsDiscountAmount" => 0.00000,
    //             // ]
    //             // ]
    //         ];
    //         $response_array = array(
    //             'success' => 1,
    //             'creditNot' => $InvData,
    //             'invoice_id' => $id

    //         );
    //     } else {
    //         $response_array = array(
    //             'success' => 0,
    //         );
    //     }
    //     // $Response = Response::json($response_array, 200);
    //     return $response_array;
    // }

    // function creditNote($invoiceId)
    // {
    //     $invoice = Einvoice::where('orders.id', $invoiceId)->first();
    //     if ($invoice) {
    //         // $invoice=get_invoice_details($invoiceId);
    //         $invoice = Einvoice::create([
    //             // "payment_id" => $invoice->payment_id,
    //             "created_at" => date('Y-m-d'),
    //             "expire_on" => $invoice->expire_on,
    //             "invoice_type" => "c",
    //             "reference_invoice" => $invoice->id,
    //         ]);
    //         $invoice->serial = 'CN-' . date("Y") . "-" . $invoice->id;
    //         $invoice->save();
    //         // $payment->amount = str_replace(',', '', convert_currency($payment->amount, 'SAR', 'EGP'));

    //         $reciever = User::where('users.id', $invoice->client_id)->join('countries', 'countries.id', 'users.country_id')
    //             ->select('users.*', 'countries.name_ar as country_name')->first();
    //         // get receiver with his country of einvoice data
    //         if (!$reciever) {
    //             return json_encode(['success' => 0, 'message' => trans('admin.reciever-exists')]);
    //         }
    //         $client = new Client(); // for client http to calling api
    //         $response_token = $this->loginTaxes();
    //         if (isset($response_token['access_token'])) {

    //             $access_token = $response_token['access_token'];

    //             $issureAddress = [
    //                 'branchId' => "0",
    //                 "country" => "EG",
    //                 "governate" => "Cairo",
    //                 "regionCity" =>  "",
    //                 "street" =>  "",
    //                 "buildingNumber" => ""
    //             ];
    //             // get receiver address
    //             $recieverAddress = [
    //                 'branchId' => "0",
    //                 "country" => "EG",
    //                 "governate" => "Cairo",
    //                 "regionCity" => "",
    //                 "street" =>  "",
    //                 "buildingNumber" => ''
    //             ];
    //             $issuer = [
    //                 'address' => $issureAddress,
    //                 "type" => "B",
    //                 "id" => einvoice_settings('tax_issuer_id'),
    //                 "name" => einvoice_settings('tax_name')
    //             ];
    //             $reciever_data = [
    //                 "address" => $recieverAddress,
    //                 "type" => "P",
    //                 "id" => "",
    //                 "name" => $reciever->name
    //             ];
    //             // get url if preprod or prod
    //             if (einvoice_settings('tax_live') == 0) {
    //                 $url = einvoice_settings('apiBaseUrlPreprodEg');
    //             } else {
    //                 $url = einvoice_settings('apiBaseUrlProdEg');
    //             }


    //             //percentage of taxes
    //             //calculate taxes
    //             $taxesRate = 14; // settings
    //             $taxes = $invoice->tax_value;
    //             if ($taxes) {
    //                 $taxType = "T1";
    //                 $subType = "V009";
    //             } else {
    //                 $taxType = '';
    //                 $subType = '';
    //             }
    //             //calculate amount without taxes
    //             $amount = $invoice->total_price_befor_tax;
    //             //prepare body of api
    //             $data = [
    //                 // "documents"=> [
    //                 [
    //                     "issuer" => $issuer,
    //                     "receiver" => $reciever_data,
    //                     "documentType" => "C",
    //                     "documentTypeVersion" => "1.0",
    //                     "dateTimeIssued" => date("Y-m-d\TH:i:s\Z"),
    //                     "taxpayerActivityCode" => einvoice_settings('tax_activity'),
    //                     "internalID" => $invoice->serial,
    //                     "invoiceLines" => [
    //                         [
    //                             "description" => "subscription",
    //                             "itemType" => einvoice_settings('tax_item_code_type'),
    //                             "itemCode" => einvoice_settings('tax_item_code'),
    //                             "unitType" => "MON",
    //                             "quantity" => 1,
    //                             "salesTotal" =>  (float)round($amount, 5),
    //                             "total" => (float)round($amount + $taxes, 5),
    //                             "valueDifference" => 0.00000,
    //                             "totalTaxableFees" => 0.00000,
    //                             "netTotal" =>  (float)round($amount, 5),
    //                             "itemsDiscount" => 0.00000,
    //                             "unitValue" => [
    //                                 "currencySold" => "EGP",
    //                                 "amountEGP" =>  (float)round($amount, 5)
    //                             ],
    //                             "discount" => [
    //                                 "rate" => 0.00000,
    //                                 "amount" => 0.00000
    //                             ],
    //                             "taxableItems" => [
    //                                 [
    //                                     "taxType" => $taxType,
    //                                     "amount" => (float)round($taxes, 5),
    //                                     "subType" => $subType,
    //                                     "rate" => $taxesRate
    //                                 ]
    //                             ]
    //                         ]
    //                     ],
    //                     "totalDiscountAmount" => 0.00000,
    //                     "totalSalesAmount" =>  (float)round($amount, 5),
    //                     "netAmount" =>  (float)round($amount, 5),
    //                     "taxTotals" => [
    //                         [
    //                             "taxType" => $taxType,
    //                             "amount" => (float)round($taxes, 5)
    //                         ]
    //                     ],
    //                     "totalAmount" => (float)round($amount + $taxes, 5),
    //                     "extraDiscountAmount" => 0.00000,
    //                     "totalItemsDiscountAmount" => 0.00000,
    //                 ]
    //                 // ]
    //             ];
    //             // $signature=signature($data['documents'][0]);
    //             // $data['documents'][0]['signatures'][]=['signatureType'=>"I",'value'=>$signature];
    //             // file_put_contents(public_path('eg_tax_invoice/invoice.json'), json_encode($data));
    //             // $signature = signature($data[0]);
    //             // $data[0]['signatures'][] = ['signatureType' => "I", 'value' => $signature];
    //             $InvData['documents'] = $data;
    //             try {
    //                 // if einvoice submmited successfully
    //                 ini_set('serialize_precision', -1);
    //                 $response = $client->request('POST', $url . '/api/v1/documentsubmissions', [
    //                     'headers'  =>  [
    //                         'Authorization' => "Bearer {$access_token}",
    //                         'content-type' => 'application/json',
    //                         'accept' => 'application/ld+json'
    //                     ],
    //                     'body' => json_encode($InvData)

    //                 ]);
    //                 $response = json_decode($response->getBody()->getContents(), true);

    //                 if ($response['submissionId']) {

    //                     $submissionDetails['uuid'] = $response['acceptedDocuments'][0]['uuid'];
    //                     $message = trans('admin.submit-success');
    //                 } else {
    //                     $submissionDetails['uuid'] = '';
    //                     $message = trans('admin.invalid-data-invoice');
    //                 }
    //                 $submissionDetails['error_msg'] = $message;
    //                 // $submissionDetails['tax_rate'] = $taxesRate;
    //                 // $submissionDetails['tax_value'] = convert_currency((float)round($taxes, 2), 'EGP', 'SAR');
    //                 // update submittion id
    //                 helper_update_by_id($submissionDetails, $invoice->id, 'orders');
    //                 return json_encode(['success' => 1, 'message' => $message, 'uuid' =>  $submissionDetails['uuid']]);
    //             } catch (GuzzleException $exception) {
    //                 // if response is invaild
    //                 $response = json_decode($exception->getResponse()->getBody(true)->getContents(), true);
    //                 if ($response) {
    //                     $errors = $response['error']; //get error message why response invalid
    //                     return json_encode(['success' => 0, 'message' => $errors]);
    //                 } else {
    //                     return json_encode(['success' => 0, 'message' => 'Unknown Error']);
    //                 }
    //             }
    //         }
    //         return json_encode(['success' => 0, 'message' => trans('admin.portal-error')]);
    //     }
    // }

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
