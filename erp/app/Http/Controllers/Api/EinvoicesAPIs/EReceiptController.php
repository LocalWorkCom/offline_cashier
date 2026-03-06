<?php

namespace App\Http\Controllers\Api\EinvoicesAPIs;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Einvoice;
use App\Models\Order;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class EReceiptController extends Controller
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
    function generateUUID($item)
    {
        // Convert the item array to a JSON string
        $jsonString = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Generate SHA256 hash in binary form
        $binaryHash = hash('sha256', $jsonString, true);

        // Convert to a hexadecimal string (64 characters)
        $hexHash = bin2hex($binaryHash);

        // Format as UUID (optional)

        return $hexHash;
    }
    function generateReceiptUUID(array $receipt): string
    {
        // Ensure UUID is empty before hashing
        $receipt['header']['uuid'] = '';

        // Serialize and normalize JSON (no extra spaces, unicode-safe)
        $normalizedText = json_encode($receipt, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Generate a SHA-256 hash (64-character hex string)
        return hash('sha256', $normalizedText);
    }
    // function convertHashToHexString($hash)
    // {
    //     // Ensure the hash is 32 bytes long (e.g., MD5 hash)
    //     if (strlen($hash) === 32) {
    //         // Convert to hexadecimal string of 64 characters (each byte becomes 2 hexadecimal characters)
    //         return bin2hex($hash);
    //     }

    //     return false; // or throw an error, depending on your use case
    // }
    function normalizeReceipt(array $receipt): string
    {
        // Ensure UUID is empty before hashing
        $receipt['header']['uuid'] = '';

        // Encode JSON without extra spaces
        return json_encode($receipt, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }



    function escapeQuotes($stringValue)
    {
        return str_replace('"', '\"', (string) ($stringValue ?? ""));
    }



    // // Helper function to check if the value is a simple type
    // function isSimpleValueType($value) {
    //     return !is_array($value);
    // }


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
        $tax_live = einvoice_settings('tax_live');
        $einvoice = Einvoice::find($invoiceId);
        // if (!$einvoice) {
        //     return json_encode([
        //         'success' => 0,
        //         'message' => 'Invoice id invalid'
        //     ]);
        // }

        // $reciever = User::where('users.id', $einvoice->order->client_id)->join('countries', 'countries.id', 'users.country_id')
        //     ->select('users.*', 'countries.name_ar as country_name')->first();
        $client = new Client();
        $response_token = $this->AuthPos();

        // if (!$reciever) {
        //     return json_encode(['success' => 0, 'message' => trans('admin.reciever-exists')]);
        // }

        if (isset($response_token['access_token'])) {
            $access_token = $response_token['access_token'];
            $url = $tax_live == 0 ? einvoice_settings('apiBaseUrlPreprodEg') : einvoice_settings('apiBaseUrlProdEg');
            // $receiptNumber = $einvoice->order->invoice_number;
            // $branch = Branch::where('id', $einvoice->order->branch_id)->first();
            // $documentType = [
            //     "receiptType" => "S",
            //     "typeVersion" => "1.2"
            // ];

            // $seller = [
            //     "rin" => einvoice_settings('tax_issuer_id'),
            //     "companyTradeName" => einvoice_settings('company_name'),
            //     "branchCode" => "11",
            //     "branchAddress" => [
            //         "country" => $branch->country->code,
            //         "governate" => $branch->governate,
            //         "regionCity" => $branch->regionCity,
            //         "street" => $branch->street,
            //         "buildingNumber" => $branch->buildingNumber,
            //         "postalCode" => "",
            //         "floor" => "",
            //         "room" => "",
            //         "landmark" => "",
            //         "additionalInformation" => ""
            //     ],
            //     "deviceSerialNumber" => "TEST12322",
            //     "syndicateLicenseNumber" => "",
            //     "activityCode" => einvoice_settings('tax_activity')
            // ];
            // $buyer = [
            //     "type" => "P",
            //     "id" => "",
            //     "name" => "",
            //     "mobileNumber" => "",
            //     "paymentNumber" => ""
            // ];
            // $TotalNetSale = 0;
            // $TotalUnitPrice = 0;
            // $TotalSales = 0;
            // $Totals = 0;
            // foreach ($einvoice->order->orderDetails as $detail) {
            //     $netSale = round($detail->price_befor_tax / $detail->quantity, 5);
            //     $unitPrice = round($detail->price_befor_tax / $detail->quantity, 5);
            //     $totalSale = round($detail->price_befor_tax, 5);
            //     $total = round($detail->price_befor_tax + $detail->tax_value, 5);
            //     $internalCode = "dish_" . str_pad($detail->dish->code, 5, "0", STR_PAD_LEFT) . "_" . time();
            //     $data = [
            //         "internalCode" => $internalCode,
            //         "description" => $detail->dish->description_en,
            //         "itemType" => "EGS",
            //         // "itemCode" => "EG-" . einvoice_settings('tax_issuer_id') . str_pad($detail->dish->code, 5, "0", STR_PAD_LEFT),
            //         "itemCode" => "675170532-1",
            //         "unitType" => "EA",
            //         "quantity" => $detail->quantity,
            //         "unitPrice" => $unitPrice,
            //         "netSale" => $netSale,
            //         "totalSale" => $totalSale,
            //         "total" => $total,
            //         "commercialDiscountData" => [
            //             [
            //                 "amount" => 0,
            //                 "description" => "",
            //                 "rate" => 0
            //             ]
            //         ],
            //         "itemDiscountData" => [

            //             [
            //                 "amount" => 0,
            //                 "description" => "",
            //                 "rate" => 0
            //             ]
            //         ],
            //         "additionalCommercialDiscount" => [
            //             "amount" => 0,
            //             "description" => "",
            //             "rate" => 0
            //         ],
            //         "additionalItemDiscount" => [
            //             "amount" => 0,
            //             "description" => "",
            //             "rate" => 0
            //         ],
            //         "valueDifference" => 0,
            //         "taxableItems" => [
            //             [
            //                 "taxType" => "T1",
            //                 "amount" => round($detail->tax_value, 5),
            //                 "subType" => "V009",
            //                 "rate" => ($detail->tax_value / $detail->price_befor_tax) * 100
            //             ],


            //         ]
            //     ];

            //     //dd($data);
            //     $TotalNetSale += $netSale;
            //     $TotalUnitPrice += $unitPrice;
            //     $TotalSales += $totalSale;
            //     $Totals += $total;
            //     $itemData[] = $data;
            // }
            // foreach ($einvoice->order->orderAddons as $addon) {
            //     $netSale = round($addon->price_befor_tax / $addon->quantity, 2);
            //     $unitPrice = round($addon->price_befor_tax / $addon->quantity, 2);
            //     $totalSale = round($addon->price_befor_tax, 2);
            //     $total = round($addon->price_after_tax, 2);
            //     $internalCode = "dish_" . str_pad($addon->Addon->addons->code, 5, "0", STR_PAD_LEFT) . "_" . time();
            //     $data = [
            //         "internalCode" => $internalCode,
            //         "description" => $addon->Addon->addons->description_en,
            //         "itemType" => "EGS",
            //         // "itemCode" => "EG-" . einvoice_settings('tax_issuer_id') . str_pad($addon->dish->code, 5, "0", STR_PAD_LEFT),
            //         "itemCode" => "EG-675170532-1",
            //         "unitType" => "EA",
            //         "quantity" => $addon->quantity,
            //         "unitPrice" => $unitPrice,
            //         "netSale" => $netSale,
            //         "totalSale" => $totalSale,
            //         "total" => $total,
            //         "valueDifference" => 0,
            //         "taxableItems" => [
            //             [
            //                 "taxType" => "T1",
            //                 "amount" => round($addon->tax_value, 2),
            //                 "subType" => "V009",
            //                 "rate" => round(($addon->tax_value / $addon->price_before_tax) * 100, 2)
            //             ],


            //         ]
            //     ];

            //     //dd($data);
            //     $TotalNetSale += $netSale;
            //     $TotalUnitPrice += $unitPrice;
            //     $TotalSales += $totalSale;
            //     $Totals += $total;
            //     $itemData[] = $data;
            // }


            // $header = [
            //     "dateTimeIssued" => gmdate("Y-m-d\TH:i:s\Z", time() - 3600),
            //     "receiptNumber" => $receiptNumber,
            //     "currency" => "EGP",
            //     "uuid" => "",
            //     "previousUUID" => "0",
            //     "referenceOldUUID" => "",
            //     "exchangeRate" => 0,
            //     "sOrderNameCode" => "sOrderNameCode",
            //     "orderdeliveryMode" => "AIR",
            //     "grossWeight" => 0.0,
            //     "netWeight" => 0.0
            // ];
            // $data = [
            //     [
            //         "header" => $header,
            //         "documentType" => $documentType,
            //         "seller" => $seller,
            //         "buyer" => $buyer,
            //         "itemData" => $itemData,
            //         "totalSales" => round($TotalSales, 5),
            //         "totalCommercialDiscount" => 0.0000,
            //         "totalItemsDiscount" => 0,

            //         "netAmount" => round($TotalNetSale, 5),
            //         // "feesAmount" => round($einvoice->order->service_fees, 2),
            //         "feesAmount" => 0,
            //         "totalAmount" => round($Totals, 5),
            //         "taxTotals" => [
            //             [
            //                 "taxType" => "T1",
            //                 "amount" => round($einvoice->order->tax_value, 5)
            //             ]
            //         ],
            //         "paymentMethod" => "C",
            //         "adjustment" => 0,
            //         "contractor" => [
            //             "name" => "",
            //             "amount" => 0,
            //             "rate" => 0
            //         ],
            //         "beneficiary" => [
            //             "amount" => 0,
            //             "rate" => 0
            //         ]
            //     ],

            // ];
            // $InvData['receipts'] = $data;

            // // Now serialize the document and update uuid
            // unset($InvData['receipts'][0]['header']['uuid']);
            // $serializedString1 = $this->serializeDocument($InvData);
            // $binaryHash = hash('sha256', $serializedString1, true);
            // $hexHash = bin2hex($binaryHash);
            // $InvData['receipts'][0]['header']['uuid'] = $hexHash;
            // $urlappend = '/api/v1/receiptsubmissions';
            // // Display JSON
            // // echo $jsonString;
            // $jsonOutput = json_encode($InvData);
            $InvData = $this->getJson();

            // Now serialize the document and update uuid
            $S =  generateReceiptWithUUIDS($InvData);
            $jsonOutput = json_encode($S, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $urlappend = '/api/v1/receiptsubmissions';
            // Display JSON
            // echo $jsonString;
            // try {
            // ini_set('serialize_precision', -1);
            $response = $client->request('POST', $url . $urlappend, [
                'headers'  =>  [
                    'Authorization' => "Bearer {$access_token}",
                    'content-type' => 'application/json',
                    'accept' => 'application/ld+json'
                ],
                'body' => $jsonOutput
            ]);
            $response = json_decode($response->getBody()->getContents(), true);
            dd($response);
            // Check if the response contains rejected documents
            if (!empty($response['rejectedDocuments'])) {
                $rejectedDocument = $response['rejectedDocuments'][0];

                // Extract errors
                $errorMessages = [];
                if (!empty($rejectedDocument['error']['details'])) {
                    foreach ($rejectedDocument['error']['details'] as $errorDetail) {
                        $errorMessages[] = "{$errorDetail['propertyPath']}: {$errorDetail['message']}";
                    }
                }
                // dd($errorMessages);
                // Prepare error response
                $submissionDetails = [
                    'uuid' => '',
                    'error_msg' => implode("; ", $errorMessages), // Concatenating error messages
                ];

                // Log the error for debugging (optional)
                // \Log::error('E-Invoice Submission Failed', $submissionDetails);

                // Update order with failure details
                helper_update_by_id($submissionDetails, $einvoice->id, 'einvoices');

                // Return JSON response with errors
                return json_encode([
                    'success' => 0,
                    'message' => 'Validation Failed: ' . implode("; ", $errorMessages),
                ]);
            }

            // Check if the submission was successful
            if (!empty($response['submissionId'])) {
                $submissionDetails = [
                    'uuid' => $response['acceptedDocuments'][0]['uuid'] ?? '',
                    'error_msg' => trans('admin.submit-success'),
                ];

                helper_update_by_id($submissionDetails, $einvoice->id, 'einvoices');

                return json_encode([
                    'success' => 1,
                    'message' => $submissionDetails['error_msg'],
                    'uuid' => $submissionDetails['uuid'],
                ]);
            }

            // If no success or failure, return unknown error
            return json_encode([
                'success' => 0,
                'message' => 'Unknown Error',
            ]);

            // } catch (GuzzleException $exception) {
            // //     // dd($exception);
            //     $response = json_decode($exception->getResponse()->getBody(true)->getContents(), true);
            //     dd($response);
            // //     if ($response) {
            // //         helper_update_by_id(['error_msg' => $response['error']], $invoice->id, 'orders');
            // //         return json_encode(['success' => 0, 'message' => $response['error']]);
            // //     }
            // //     return json_encode(['success' => 0, 'message' => 'Unknown Error']);
            // }
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
        $response_token = $this->AuthPos();
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

    function AuthPos()
    {
        $tax_live = einvoice_settings('tax_live');

        $client = new Client();
        if ($tax_live == 0) {
            $url = einvoice_settings('idSrvBaseUrlPreprodEg');
            $tax_client_id = einvoice_settings('tax_client_id');
            $tax_secret_id = einvoice_settings('tax_secret_id');
        } else {
            $url = einvoice_settings('apiBaseUrlProdEg');
            $tax_client_id = einvoice_settings('tax_client_id_live');
            $tax_secret_id = einvoice_settings('tax_secret_id_live');
        }
        $response = $client->request('POST', $url . '/connect/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $tax_client_id,
                'client_secret' => $tax_secret_id,
            ],
            // 'headers'  =>  [
            //     'posserial' => 'TEST12322', //Test_SerialNo1
            //     'pososversion' => 'IOS', //IOS
            //     'posmodelframework' => '1', //1
            //     'presharedkey' => 'C68FEC13-FE34-4839-89F9-ADB0D678CFE0' //
            // ],
        ]);
        $response = json_decode($response->getBody()->getContents(), true);
        return $response;
    }


    function serializeDocumentNew($documentStructure, $parentKey = null)
    {
        if (!is_array($documentStructure) || empty($documentStructure)) {
            return '"' . $documentStructure . '"';
        }
        $serializedString = "";
        foreach ($documentStructure as $key => $value) {
            $currentKey = is_numeric($key) ? $parentKey : strtoupper($key);
            $serializedString .= '"' . strtoupper($currentKey) . '"';

            if (is_array($value)) {
                $serializedString .= $this->serializeDocumentNew($value, $currentKey);
            } else {
                $serializedString .= $this->serializeDocumentNew($value);
            }
        }
        return $serializedString;
    }

    function generateReceiptWithUUIDS($receipts)
    {
        $receiptsArray = $receipts;
        foreach ($receiptsArray['receipts'] as $key => $receipt) {
            $uuid = hash('sha256', $this->serializeDocumentNew($receipt));
            $receiptsArray['receipts'][$key]['header']['uuid'] = $uuid;
        }
        return $receiptsArray;
    }
    function getJson()
    {
        return [
            'receipts' => [
                [
                    'header' => [
                        'dateTimeIssued' => '2025-05-12T07:14:46Z',
                        'receiptNumber' => 'INV-ST-1140',
                        'currency' => 'EGP',
                        'uuid' => '',
                        'previousUUID' => '',
                        'referenceOldUUID' => '',
                        'exchangeRate' => 0,
                        'sOrderNameCode' => '',
                        'orderdeliveryMode' => 'FC',
                        'grossWeight' => 0,
                        'netWeight' => 0
                    ],
                    'documentType' => [
                        'receiptType' => 'S',
                        'typeVersion' => '1.2'
                    ],
                    'seller' => [
                        'rin' => '675170532',
                        'companyTradeName' => 'لا كوتشينا للضيافه',
                        'branchCode' => '0',
                        'branchAddress' => [
                            'country' => 'EG',
                            'governate' => 'المهندسين',
                            'regionCity' => 'عرابي',
                            'street' => 'Default Street',
                            'buildingNumber' => '122',
                            'postalCode' => '',
                            'floor' => '',
                            'room' => '',
                            'landmark' => '',
                            'additionalInformation' => ''
                        ],
                        'deviceSerialNumber' => 'TEST12322',
                        'syndicateLicenseNumber' => '',
                        'activityCode' => '5610'
                    ],
                    'buyer' => [
                        'type' => 'P',
                        'id' => '',
                        'name' => '',
                        'mobileNumber' => '',
                        'paymentNumber' => ''
                    ],
                    'itemData' => [
                        [
                            'internalCode' => '0006',
                            'description' => 'عيش مع قطع دجاج وصوص بهارات وبصل وخيار وروب',
                            'itemType' => 'EGS',
                            'itemCode' => 'EG-675170532-100',
                            'unitType' => 'EA',
                            'quantity' => 1,
                            'unitPrice' => 50,
                            'netSale' => 50,
                            'totalSale' => 50,
                            'total' => 63.84,
                            'commercialDiscountData' => [['amount' => 0, 'description' => 'na', 'rate' => 0]],
                            'itemDiscountData' => [['amount' => 0, 'description' => 'na', 'rate' => 0]],
                            'additionalCommercialDiscount' => ['amount' => 0, 'description' => 'na', 'rate' => 0],
                            'additionalItemDiscount' => ['amount' => 0, 'description' => 'na', 'rate' => 0],
                            'valueDifference' => 0,
                            'taxableItems' => [
                                ['taxType' => 'T1', 'amount' => 7.84, 'subType' => 'V009', 'rate' => 14],
                                ['taxType' => 'T9', 'amount' => 6, 'subType' => 'SC01', 'rate' => 12]
                            ]
                        ],
                        [
                            'internalCode' => '0001',
                            'description' => 'new',
                            'itemType' => 'EGS',
                            'itemCode' => 'EG-675170532-100',
                            'unitType' => 'EA',
                            'quantity' => 1,
                            'unitPrice' => 11,
                            'netSale' => 11,
                            'totalSale' => 11,
                            'total' => 14.04,
                            'commercialDiscountData' => [['amount' => 0, 'description' => 'na', 'rate' => 0]],
                            'itemDiscountData' => [['amount' => 0, 'description' => 'na', 'rate' => 0]],
                            'additionalCommercialDiscount' => ['amount' => 0, 'description' => 'na', 'rate' => 0],
                            'additionalItemDiscount' => ['amount' => 0, 'description' => 'na', 'rate' => 0],
                            'valueDifference' => 0,
                            'taxableItems' => [
                                ['taxType' => 'T1', 'amount' => 1.72, 'subType' => 'V009', 'rate' => 14],
                                ['taxType' => 'T9', 'amount' => 1.32, 'subType' => 'SC01', 'rate' => 12]
                            ]
                        ],
                        [
                            'internalCode' => '0002',
                            'description' => 'desc',
                            'itemType' => 'EGS',
                            'itemCode' => 'EG-675170532-100',
                            'unitType' => 'EA',
                            'quantity' => 1,
                            'unitPrice' => 12,
                            'netSale' => 12,
                            'totalSale' => 12,
                            'total' => 15.32,
                            'commercialDiscountData' => [['amount' => 0, 'description' => 'na', 'rate' => 0]],
                            'itemDiscountData' => [['amount' => 0, 'description' => 'na', 'rate' => 0]],
                            'additionalCommercialDiscount' => ['amount' => 0, 'description' => 'na', 'rate' => 0],
                            'additionalItemDiscount' => ['amount' => 0, 'description' => 'na', 'rate' => 0],
                            'valueDifference' => 0,
                            'taxableItems' => [
                                ['taxType' => 'T1', 'amount' => 1.88, 'subType' => 'V009', 'rate' => 14],
                                ['taxType' => 'T9', 'amount' => 1.44, 'subType' => 'SC01', 'rate' => 12]
                            ]
                        ]
                    ],
                    'totalSales' => 73,
                    'totalCommercialDiscount' => 0,
                    'totalItemsDiscount' => 0,
                    'extraReceiptDiscountData' => [['amount' => 0, 'description' => 'na', 'rate' => 0]],
                    'netAmount' => 73,
                    'feesAmount' => 0,
                    'totalAmount' => 93.2,
                    'taxTotals' => [
                        ['taxType' => 'T1', 'amount' => 11.45],
                        ['taxType' => 'T9', 'amount' => 8.76]
                    ],
                    'paymentMethod' => 'C',
                    'adjustment' => 0,
                    'contractor' => ['name' => '', 'amount' => 0, 'rate' => 0],
                    'beneficiary' => ['amount' => 0, 'rate' => 0]
                ]
            ]
        ];
    }
}
