<?php

use App\Models\Branch;
use App\Models\Einvoice;
use App\Models\Invoice;
use Illuminate\Support\Facades\Redirect;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;


function SendPortal($selectedInvoices)
{
    $client = new Client();

    // $einvoices = explode(',', $selectedInvoices);
    $einvoices = $selectedInvoices;
    $results = [];

    foreach ($einvoices as $einvoice) {
        $einvoice_data = Einvoice::find($einvoice);
        $response_token = AuthPos($einvoice_data->invoice->orders->branch_id);

        if (!isset($response_token['access_token'])) {
            return Redirect::back()->withErrors(trans('admin.portal-error'))->withInput();
        }

        $access_token = $response_token['access_token'];
        $branch = Branch::find($einvoice_data->invoice->orders->branch_id);
        $tax_live = $branch->is_live;

        $url = $tax_live == 0 ? einvoice_settings('apiBaseUrlPreprodEg') : einvoice_settings('apiBaseUrlProdEg');
        $urlappend = '/api/v1/receiptsubmissions';
        if ($einvoice_data->invoice->coupon_value == 0) {
            $invoiceResult = GenerateJsonInvoice($einvoice);
        } else {
            $invoiceResult = GenerateJsonInvoiceCoupon($einvoice);
        }
        if (isset($invoiceResult['error'])) {
            // Log or handle the missing itemCode
            helper_update_by_id(['error_msg' => $invoiceResult['message']], $einvoice, 'einvoices');
            $results[] = ['invoice' => $einvoice, 'status' => 'error', 'msg' => $invoiceResult['message']];
            continue;
        }
        [$InvData, $time] = $invoiceResult;
        $jsonOutput = json_encode($InvData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        try {
            $response = $client->request('POST', $url . $urlappend, [
                'headers' => [
                    'Authorization' => "Bearer {$access_token}",
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/ld+json',
                ],
                'body' => $jsonOutput,
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);
            // Handle rejected documents
            if (!empty($responseBody['rejectedDocuments'])) {
                $errors = [];
                foreach ($responseBody['rejectedDocuments'][0]['error']['details'] ?? [] as $errorDetail) {
                    $errors[] = "{$errorDetail['propertyPath']}: {$errorDetail['message']}";
                }

                $submissionDetails = [
                    'uuid' => '',
                    'error_msg' => implode("; ", $errors),
                ];

                helper_update_by_id($submissionDetails, $einvoice, 'einvoices');
                $results[] = ['invoice' => $einvoice, 'status' => 'error', 'msg' => $submissionDetails['error_msg']];
                continue;
            }

            // Handle accepted documents
            if (!empty($responseBody['submissionId'])) {
                $submissionDetails = [
                    'uuid' => $responseBody['acceptedDocuments'][0]['uuid'] ?? '',
                    'submissionId' => $responseBody['submissionId'],
                    'error_msg' => trans('einvoice.submit-success'),
                    'dateTimeIssued' => $time,
                    'public_urls' => 'https://preprod.invoicing.eta.gov.eg/receipts/search/' .
                        $responseBody['acceptedDocuments'][0]['uuid'] . '/share/' . trim($time)
                ];

                helper_update_by_id($submissionDetails, $einvoice, 'einvoices');
                $results[] = ['invoice' => $einvoice, 'status' => 'success', 'msg' => $submissionDetails['error_msg']];
            } else {
                $results[] = ['invoice' => $einvoice, 'status' => 'error', 'msg' => 'Unknown submission response.'];
            }
        } catch (GuzzleException $exception) {
            $errorMsg = 'Unknown API error';
            if ($exception->hasResponse()) {
                $errorResponse = json_decode($exception->getResponse()->getBody()->getContents(), true);
                $errorMsg = $errorResponse['error'] ?? $errorMsg;
                helper_update_by_id(['error_msg' => $errorMsg], $einvoice, 'orders');
            }
            $results[] = ['invoice' => $einvoice, 'status' => 'error', 'msg' => $errorMsg];
        }
    }

    return $results;
}

function GenerateJsonInvoiceCoupon($einvoice_id)
{
    $einvoice = Einvoice::find($einvoice_id);
    if (!$einvoice) {
        return json_encode([
            'error' => true,
            'message' => 'Invoice id invalid'
        ]);
    }

    // $reciever = User::where('users.id', $einvoice->order->client_id)->join('countries', 'countries.id', 'users.country_id')
    //     ->select('users.*', 'countries.name_ar as country_name')->first();
    // if (!$reciever) {
    //     return json_encode(['success' => 0, 'message' => trans('admin.reciever-exists')]);
    // }

    $receiptNumber = $einvoice->invoice->invoice_num;
    $order_type = $einvoice->invoice->orders->type;
    $branch = Branch::find($einvoice->invoice->orders->branch_id);
    if ($einvoice->invoice_type == 'c') {

        $documentType = [
            "receiptType" => "r",
            "typeVersion" => "1.2"
        ];
    } else {

        $documentType = [
            "receiptType" => "S",
            "typeVersion" => "1.2"
        ];
    }

    $seller = [
        "rin" => einvoice_settings('tax_issuer_id'),
        "companyTradeName" => einvoice_settings('company_name'),
        "branchCode" => $branch->code,
        "branchAddress" => [
            "country" => "EG",
            "governate" => $branch->city->name,
            "regionCity" => $branch->area->name,
            "street" => $branch->street,
            "buildingNumber" => $branch->buildingNumber,
            "postalCode" => "",
            "floor" => "",
            "room" => "",
            "landmark" => "",
            "additionalInformation" => ""
        ],
        // "deviceSerialNumber" => $einvoice->order->device_id,
        "deviceSerialNumber" => "TEST12322",
        "syndicateLicenseNumber" => "",
        "activityCode" => einvoice_settings('tax_activity')
    ];
    $buyer = [
        "type" => "P",
        "id" => "",
        "name" => "",
        "mobileNumber" => "",
        "paymentNumber" => ""
    ];
    $TotalNetSale = 0;
    $TotalUnitPrice = 0;
    $TotalSales = 0;
    $Totals = 0;
    $TotalService = 0;
    $TotalTax = 0;
    foreach ($einvoice->invoice->invoiceDetails as $index => $detail) {

        $type = $detail->type;
        $netSale = $detail->total_before_coupon;
        $unitPrice = $detail->total_before_coupon / $detail->quantity;
        $totalSale = $detail->total_before_coupon;
        // $total = $detail->total_after_tax;
        $tax_rate = $einvoice->invoice->orders->tax_percentage;
        $service_rate = $einvoice->invoice->orders->service_percentage;
        $service = $detail->total_before_coupon * $service_rate / 100;
        $totalAfterService = $service + $detail->total_before_coupon;
        $tax  =  $totalAfterService * $tax_rate / 100;
        $total = $service + $tax + $detail->total_before_coupon;

        $internalCode = $type == 'dish' ? $detail->OrderDetails->dish->code : $detail->OrderDetails->Addon->addons->code;
        // $itemCode = $detail->OrderDetails->dish->itemCode->itemCode;
        // if (!$itemCode) {
        //     return [
        //         'error' => true,
        //         'message' => "Missing itemCode for invoice ID: {$einvoice_id}",
        //     ];
        // }
        $taxableItems = [
            [
                "taxType" => "T1",
                "amount" => (float) round($tax, 2),
                "subType" => "V009",
                "rate" => (float) $tax_rate,
            ]
        ];

        if ($order_type == 'dine-in' || $order_type == 'reservation-table') {
            if ($service_rate == 0) {
                return [
                    'error' => true,
                    'message' => "Service Can't be zero",
                ];
            }
            $taxableItems[] = [
                "taxType" => "T9",
                "amount" => (float) round($service, 2),
                "subType" => "SC01",
                "rate" => (float) $service_rate,
            ];
        }


        $data = [
            "internalCode" => $internalCode,
            "description" => $type == 'dish' ? $detail->OrderDetails->dish->description : $detail->OrderDetails->Addon->addons->description,
            "itemType" => "EGS",
            // "itemCode" => "EG-" . einvoice_settings('tax_issuer_id') . str_pad($detail->dish->code, 5, "0", STR_PAD_LEFT),
            "itemCode" => "EG-675170532-100",
            // "itemCode" => $itemCode,
            "unitType" => "EA",
            "quantity" => $detail->quantity,
            "unitPrice" => round($unitPrice, 2),
            "netSale" => round($netSale, 2),
            "totalSale" => round($totalSale, 2),
            "total" => round($total, 2),
            "commercialDiscountData" =>  [
                [
                    "amount" => 0,
                    "description" => "na",
                    "rate" => 0
                ]
            ],
            "itemDiscountData" => [
                [
                    "amount" => 0,
                    "description" => "na",
                    "rate" => 0
                ]
            ],
            "additionalCommercialDiscount" => [
                "amount" => 0,
                "description" => "na",
                "rate" => 0
            ],
            "additionalItemDiscount" =>  [
                "amount" => 0,
                "description" => "na",
                "rate" => 0
            ],
            "valueDifference" => 0,
            "taxableItems" => $taxableItems
        ];

        $TotalNetSale += $netSale;
        $TotalUnitPrice += $unitPrice;
        $TotalSales += $totalSale;
        $Totals += $total;
        $TotalTax += $tax;
        $TotalService += $service;
        $itemData[] = $data;
    }

    $time = gmdate("Y-m-d\TH:i:s\Z", time() - 3600);
    $header = [
        "dateTimeIssued" => $time,
        "receiptNumber" => $receiptNumber,
        "currency" => "EGP",
        "uuid" => "",
        "previousUUID" => "",
        "referenceOldUUID" => "",
        "exchangeRate" => 0,
        "sOrderNameCode" => "",
        "orderdeliveryMode" => "FC",
        "grossWeight" => 0,
        "netWeight" => 0
    ];

    // أضف المفتاح فقط إذا كانت الفاتورة من نوع credit
    if ($einvoice->invoice_type == 'c') {
        $parent_invoice = Invoice::where('id', $einvoice->invoice->parent_id)->first();
        $parent_einvoice = Einvoice::where("invoice_id", $parent_invoice->id)->first();
        $referenceUUID = $parent_einvoice->uuid ?? null;

        if (!$referenceUUID) {
            return [
                'error' => true,
                'message' => "Please Send Original Invoice First Before Return Invocie",
            ];
        }

        $header["referenceUUID"] = $referenceUUID;
    }

    $taxTotals = [
        [
            "taxType" => "T1",
            "amount" => (float) round($TotalTax, 2)
        ]
    ];

    if ($order_type == 'dine-in' || $order_type == 'reservation-table') {
        $taxTotals[] =   [
            "taxType" => "T9",
            "amount" => (float) round($TotalService, 2)
        ];
    }

    $data = [
        [
            "header" => $header,
            "documentType" => $documentType,
            "seller" => $seller,
            "buyer" => $buyer,
            "itemData" => $itemData,
            "totalSales" => $TotalSales,
            "totalCommercialDiscount" => 0,
            "totalItemsDiscount" => 0,
            "extraReceiptDiscountData" => [
                [
                    "amount" => 0,
                    "description" => "na",
                    "rate" => 0
                ]
            ],
            "netAmount" => $TotalNetSale,
            "feesAmount" => 0,
            "totalAmount" => round($Totals, 2),
            "taxTotals" => $taxTotals,
            // "paymentMethod" => $einvoice->order->transaction->payment_method,
            "paymentMethod" => "C",
            "adjustment" => 0,
            "contractor" => [
                "name" => "",
                "amount" => 0,
                "rate" => 0
            ],
            "beneficiary" => [
                "amount" => 0,
                "rate" => 0
            ]
        ],

    ];
    $InvData['receipts'] = $data;
    $ArrayOutput =  generateReceiptWithUUIDS($InvData);

    return [$ArrayOutput, $time];
}
function GenerateJsonInvoice($einvoice_id)
{
    $einvoice = Einvoice::find($einvoice_id);
    if (!$einvoice) {
        return json_encode([
            'error' => true,
            'message' => 'Invoice id invalid'
        ]);
    }

    // $reciever = User::where('users.id', $einvoice->order->client_id)->join('countries', 'countries.id', 'users.country_id')
    //     ->select('users.*', 'countries.name_ar as country_name')->first();
    // if (!$reciever) {
    //     return json_encode(['success' => 0, 'message' => trans('admin.reciever-exists')]);
    // }

    $receiptNumber = $einvoice->invoice->invoice_num;
    $order_type = $einvoice->invoice->orders->type;
    $branch = Branch::find($einvoice->invoice->orders->branch_id);
    if ($einvoice->invoice_type == 'c') {

        $documentType = [
            "receiptType" => "r",
            "typeVersion" => "1.2"
        ];
    } else {

        $documentType = [
            "receiptType" => "S",
            "typeVersion" => "1.2"
        ];
    }

    $seller = [
        "rin" => einvoice_settings('tax_issuer_id'),
        "companyTradeName" => einvoice_settings('company_name'),
        "branchCode" => $branch->code,
        "branchAddress" => [
            "country" => "EG",
            "governate" => $branch->city->name,
            "regionCity" => $branch->area->name,
            "street" => $branch->street,
            "buildingNumber" => $branch->buildingNumber,
            "postalCode" => "",
            "floor" => "",
            "room" => "",
            "landmark" => "",
            "additionalInformation" => ""
        ],
        // "deviceSerialNumber" => $einvoice->order->device_id,
        "deviceSerialNumber" => "TEST12322",
        "syndicateLicenseNumber" => "",
        "activityCode" => einvoice_settings('tax_activity')
    ];
    $buyer = [
        "type" => "P",
        "id" => "",
        "name" => "",
        "mobileNumber" => "",
        "paymentNumber" => ""
    ];
    $TotalNetSale = 0;
    $TotalUnitPrice = 0;
    $TotalSales = 0;
    $Totals = 0;
    foreach ($einvoice->invoice->invoiceDetails as $index => $detail) {

        $type = $detail->type;
        $netSale = $detail->total_before_tax;
        $unitPrice = $detail->total_before_tax / $detail->quantity;
        $totalSale = $detail->total_before_tax;
        $total = $detail->total_after_tax;
        $tax_rate = $einvoice->invoice->orders->tax_percentage;
        $service_rate = $einvoice->invoice->orders->service_percentage;
        $internalCode = $type == 'dish' ? $detail->OrderDetails->dish->code : $detail->OrderDetails->Addon->addons->code;
        // $itemCode = $detail->OrderDetails->dish->itemCode->itemCode;
        // if (!$itemCode) {
        //     return [
        //         'error' => true,
        //         'message' => "Missing itemCode for invoice ID: {$einvoice_id}",
        //     ];
        // }
        $taxableItems = [
            [
                "taxType" => "T1",
                "amount" => (float) round($detail->tax, 2),
                "subType" => "V009",
                "rate" => (float) $tax_rate,
            ]
        ];

        if ($order_type == 'dine-in' || $order_type == 'reservation-table') {
            if ($service_rate == 0) {
                return [
                    'error' => true,
                    'message' => "Service Can't be zero",
                ];
            }
            $taxableItems[] = [
                "taxType" => "T9",
                "amount" => (float) round($detail->service_fees, 2),
                "subType" => "SC01",
                "rate" => (float) $service_rate,
            ];
        }



        $data = [
            "internalCode" => $internalCode,
            "description" => $type == 'dish' ? $detail->OrderDetails->dish->description : $detail->OrderDetails->Addon->addons->description,
            "itemType" => "EGS",
            // "itemCode" => "EG-" . einvoice_settings('tax_issuer_id') . str_pad($detail->dish->code, 5, "0", STR_PAD_LEFT),
            "itemCode" => "EG-675170532-100",
            // "itemCode" => $itemCode,
            "unitType" => "EA",
            "quantity" => $detail->quantity,
            "unitPrice" => round($unitPrice, 2),
            "netSale" => round($netSale, 2),
            "totalSale" => round($totalSale, 2),
            "total" => round($total, 2),
            "commercialDiscountData" =>  [
                [
                    "amount" => 0,
                    "description" => "na",
                    "rate" => 0
                ]
            ],
            "itemDiscountData" => [
                [
                    "amount" => 0,
                    "description" => "na",
                    "rate" => 0
                ]
            ],
            "additionalCommercialDiscount" => [
                "amount" => 0,
                "description" => "na",
                "rate" => 0
            ],
            "additionalItemDiscount" =>  [
                "amount" => 0,
                "description" => "na",
                "rate" => 0
            ],
            "valueDifference" => 0,
            "taxableItems" => $taxableItems
        ];

        $TotalNetSale += $netSale;
        $TotalUnitPrice += $unitPrice;
        $TotalSales += $totalSale;
        $Totals += $total;
        $itemData[] = $data;
    }

    $time = gmdate("Y-m-d\TH:i:s\Z", time() - 3600);
    $header = [
        "dateTimeIssued" => $time,
        "receiptNumber" => $receiptNumber,
        "currency" => "EGP",
        "uuid" => "",
        "previousUUID" => "",
        "referenceOldUUID" => "",
        "exchangeRate" => 0,
        "sOrderNameCode" => "",
        "orderdeliveryMode" => "FC",
        "grossWeight" => 0,
        "netWeight" => 0
    ];

    // أضف المفتاح فقط إذا كانت الفاتورة من نوع credit
    if ($einvoice->invoice_type == 'c') {
        $parent_invoice = Invoice::where('id', $einvoice->invoice->parent_id)->first();
        $parent_einvoice = Einvoice::where("invoice_id", $parent_invoice->id)->first();
        $referenceUUID = $parent_einvoice->uuid ?? null;

        if (!$referenceUUID) {
            return [
                'error' => true,
                'message' => "Please Send Original Invoice First Before Return Invocie",
            ];
        }

        $header["referenceUUID"] = $referenceUUID;
    }

    $taxTotals = [
        [
            "taxType" => "T1",
            "amount" => (float) round($einvoice->invoice->tax, 2)
        ]
    ];

    if ($order_type == 'dine-in' || $order_type == 'reservation-table') {
        $taxTotals[] =   [
            "taxType" => "T9",
            "amount" => (float) round($einvoice->invoice->service_fees, 2)
        ];
    }

    $data = [
        [
            "header" => $header,
            "documentType" => $documentType,
            "seller" => $seller,
            "buyer" => $buyer,
            "itemData" => $itemData,
            "totalSales" => $TotalSales,
            "totalCommercialDiscount" => 0,
            "totalItemsDiscount" => 0,
            "extraReceiptDiscountData" => [
                [
                    "amount" => 0,
                    "description" => "na",
                    "rate" => 0
                ]
            ],
            "netAmount" => $TotalNetSale,
            "feesAmount" => 0,
            "totalAmount" => round($Totals, 2),
            "taxTotals" => $taxTotals,
            // "paymentMethod" => $einvoice->order->transaction->payment_method,
            "paymentMethod" => "C",
            "adjustment" => 0,
            "contractor" => [
                "name" => "",
                "amount" => 0,
                "rate" => 0
            ],
            "beneficiary" => [
                "amount" => 0,
                "rate" => 0
            ]
        ],

    ];
    $InvData['receipts'] = $data;
    $ArrayOutput =  generateReceiptWithUUIDS($InvData);

    return [$ArrayOutput, $time];
}
function AuthPos($branch_id)
{
    $branch = Branch::find($branch_id);
    $tax_live = $branch->is_live;
    // $tax_live = einvoice_settings('tax_live');

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
            $serializedString .= serializeDocumentNew($value, $currentKey);
        } else {
            $serializedString .= serializeDocumentNew($value);
        }
    }
    return $serializedString;
}

function generateReceiptWithUUIDS($receipts)
{
    $receiptsArray = $receipts;
    foreach ($receiptsArray['receipts'] as $key => $receipt) {
        $uuid = hash('sha256', serializeDocumentNew($receipt));
        $receiptsArray['receipts'][$key]['header']['uuid'] = $uuid;
    }
    return $receiptsArray;
}
function GetReceiptDetails($einvoice_id)
{
    $client = new Client(); // HTTP client for API calls

    $einvoice = Einvoice::find($einvoice_id);

    if (!$einvoice || !$einvoice->uuid) {
        return ['error' => true, 'message' => 'Invoice ID is invalid or missing UUID.'];
    }

    $response_token = AuthPos($einvoice->invoice->orders->branch_id);

    if (!isset($response_token['access_token'])) {
        return ['error' => true, 'message' => trans('admin.portal-error')];
    }

    $access_token = $response_token['access_token'];
    $branch = Branch::find($einvoice->invoice->orders->branch_id);

    $tax_live = $branch->is_live;

    $tax_live = einvoice_settings('tax_live');
    $url = $tax_live == 0 ? einvoice_settings('apiBaseUrlPreprodEg') : einvoice_settings('apiBaseUrlProdEg');

    try {
        $urlappend = '/api/v1/receipts/' . $einvoice->uuid . '/details';

        $response = $client->request('GET', $url . $urlappend, [
            'headers' => [
                'Authorization' => "Bearer {$access_token}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/ld+json'
            ]
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);
        if (isset($responseData['submissionUuid'])) {
            $receipt = $responseData['receipt'];
            if (isset($receipt['referenceUUID'])) {

                $referenceUUID = $receipt['referenceUUID'];
                if ($referenceUUID) {

                    $refernceEinvoice = Einvoice::where('uuid', $referenceUUID)->first();
                }
            }
            $einvoice->submission_date = $responseData['dateTimeReceived'] ?? null;
            $einvoice->old_uuid = $receipt['referenceOldUUID'] ?? null;
            $einvoice->long_id = $receipt['longId'] ?? null;
            $einvoice->previous_uuid = $receipt['previousUUID'] ?? null;
            $einvoice->status = $receipt['status'] ?? null;
            $einvoice->has_return_receipts = $receipt['hasReturnReceipts'] ?? false;
            $einvoice->reference_invoice_id = isset($referenceUUID) ? $refernceEinvoice->id : null;
            $einvoice->save();

            return ['success' => true, 'message' => 'Details fetched and stored successfully.'];
        } else {
            return ['error' => true, 'message' => 'API response did not contain expected data.'];
        }
    } catch (GuzzleException $exception) {
        $einvoice->status = 'Invalid';
        $einvoice->save();

        $errorMessage = $exception->getMessage();
        if ($exception->getResponse()) {
            $errorDetails = json_decode($exception->getResponse()->getBody()->getContents(), true);
            $errorMessage = $errorDetails['error'] ?? $errorMessage;
        }

        return ['error' => true, 'message' => "API Error: $errorMessage"];
    }
}
function CreateEGSCode($dish_id)
{
    // $client = new Client(); // HTTP client for API calls
    // $response_token = AuthPos();

    // try {
    //     $urlappend = '/api/v1/receipts/' . $einvoice->uuid . '/details';

    //     $response = $client->request('GET', $url . $urlappend, [
    //         'headers' => [
    //             'Authorization' => "Bearer {$access_token}",
    //             'Content-Type' => 'application/json',
    //             'Accept' => 'application/ld+json'
    //         ]
    //     ]);

    //     $responseData = json_decode($response->getBody()->getContents(), true);
    // } catch (GuzzleException $exception) {

    // }

}
