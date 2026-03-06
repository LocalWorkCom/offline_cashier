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

class EReceiptNewController extends Controller
{

    function erecepit2()
    {
        $InvData = '{
            "receipts": [
                {
                    "header": {
                        "dateTimeIssued": "2025-02-02T13:00:57Z",
                        "receiptNumber": "1234566",
                        "uuid": "",
                        "previousUUID": "",
                        "referenceOldUUID": "",
                        "currency": "EGP",
                        "exchangeRate": 0,
                        "sOrderNameCode": "sOrderNameCode",
                        "orderdeliveryMode": "AIR",
                        "grossWeight": 6.43,
                        "netWeight": 6.89
                    },
                    "documentType": {
                        "receiptType": "S",
                        "typeVersion": "1.2"
                    },
                    "seller": {
                        "rin": "675170532",
                        "companyTradeName": " تويوتا مصر للتجارة ",
                        "branchCode": "0",
                        "branchAddress": {
                            "country": "EG",
                            "governate": "cairo",
                            "regionCity": "city center",
                            "street": "14 street",
                            "buildingNumber": "18",
                            "postalCode": "74235",
                            "floor": "1",
                            "room": "3",
                            "landmark": "tahrir square",
                            "additionalInformation": "talaat harb street"
                        },
                        "deviceSerialNumber": "654321",
                        "syndicateLicenseNumber": "1000056",
                        "activityCode": "4620"
                    },
                    "buyer": {
                        "type": "P",
                        "id": "",
                        "name": "",
                        "mobileNumber": "",
                        "paymentNumber": ""
                    },
                    "itemData": [
                                {
                                "internalCode": "8806092129306",
                                "description": "Samsung A02 32GB_LTE_BLACK_DS_SM-A022FZKDMEB_A022 _ A022_SM-A022FZKDMEB",
                                "itemType": "GS1",
                                "itemCode": "6221218058490",
                                "unitType": "EA",
                                "quantity": 7,
                                "unitPrice": 13.5,
                                "netSale": 92.988,
                                "totalSale": 94.5,
                                "total": 96.95192,
                                "commercialDiscountData": [
                                {
                                "amount": 0.512,
                                "description": "XYZ"
                                },
                                {
                                "amount": 1,
                                "description": "XYZABC"
                                }
                                ],
                                "itemDiscountData": [
                                {
                                "amount": 2.8,
                                "description": "ABC"
                                },
                                {
                                "amount": 1.1,
                                "description": "XYZ"
                                }
                                ],
                                "valueDifference": -5,
                                "taxableItems": [
                                {
                                "taxType": "T1",
                                "subType": "V001",
                                "amount": 12.31832,
                                "rate": 14
                                },
                                {
                                "taxType": "T4",
                                "subType": "W001",
                                "amount": 4.4544,
                                "rate": 5
                                }
                                ]
                                },
                                {
                                "internalCode": "10000019",
                                "description": "Petcare",
                                "itemType": "GS1",
                                "itemCode": "6221218058490",
                                "unitType": "EA",
                                "quantity": 89,
                                "unitPrice": 150.987,
                                "netSale": 13303.46457,
                                "totalSale": 13437.843,
                                "total": 14762.15667,
                                "commercialDiscountData": [
                                {
                                "amount": 34.37843,
                                "description": "XYZ"
                                },
                                {
                                "amount": 100,
                                "description": "XYZABC"
                                }
                                ],
                                "itemDiscountData": [
                                {
                                "amount": 2,
                                "description": "ABC"
                                },
                                {
                                "amount": 3.7,
                                "description": "XYZ"
                                }
                                ],
                                "valueDifference": 6,
                                "taxableItems": [
                                {
                                "taxType": "T1",
                                "subType": "V009",
                                "amount": 1863.32504,
                                "rate": 14
                                },
                                {
                                "taxType": "T4",
                                "subType": "W001",
                                "amount": 398.93294,
                                "rate": 3
                                }
                                ]
                                }
                                ],
                    "totalSales": 13532.343,
                    "totalCommercialDiscount": 135.89043,
                    "totalItemsDiscount": 9.6,
                    "extraReceiptDiscountData": [
                        {
                            "amount": 25.49,
                            "description": "ABC"
                        }
                    ],
                    "netAmount": 13396.45257,
                    "feesAmount": 0,
                    "totalAmount": 14808.12859,
                    "taxTotals": [
                        {
                            "taxType": "T1",
                            "amount": 1875.64336
                        },
                        {
                            "taxType": "T4",
                            "amount": 403.38734
                        }
                    ],
                    "paymentMethod": "Cash",
                    "contractor": {
                        "name": "contractor1",
                        "amount": 2.563,
                        "rate": 2.3
                    },
                    "beneficiary": {
                        "amount": 20.569,
                        "rate": 2.147
                    }
                }
            ]
        }';

        $dataArray = json_decode($InvData, true);

        $normalizedText = $this->serializeData($dataArray);

        // Generate SHA256 Hash (Raw binary output)
        $rawHash = hash('sha256', $normalizedText, true);

        // Convert raw binary hash to hexadecimal (64-character string)
        $hexHash = bin2hex($rawHash);

        return response()->json([
            'normalized_text' => $normalizedText,
            'sha256_hash' => $hexHash // This is the correct UUID of 64 characters
        ]);


    }

    function serializeData($documentStructure, $key = "")
    {
        if (!is_array($documentStructure) && !is_object($documentStructure)) {
            return strtoupper($key) . ":" . strval($documentStructure) . " ";
        }

        $serializedString = "";

        foreach ($documentStructure as $name => $value) {
            $upperName = strtoupper($name);

            if (is_array($value)) {
                foreach ($value as $arrayElement) {
                    $serializedString .= $upperName . " " . $this->serializeData($arrayElement, $upperName);
                }
            } else {
                $serializedString .= $upperName . " " . $this->serializeData($value, $upperName);
            }
        }

        return $serializedString;
    }




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

    // function convertHashToHexString($hash)
    // {
    //     // Ensure the hash is 32 bytes long (e.g., MD5 hash)
    //     if (strlen($hash) === 32) {
    //         // Convert to hexadecimal string of 64 characters (each byte becomes 2 hexadecimal characters)
    //         return bin2hex($hash);
    //     }

    //     return false; // or throw an error, depending on your use case
    // }

    function serializeDocumentOld($documentStructure) {
        $serializedString = "";
    
        foreach ($documentStructure as $key => $value) {
            // Convert the key to uppercase
            $serializedString .= '"' . strtoupper($key) . '"';
    
            if (is_array($value)) {
                // If the value is an array, recursively serialize it
                $serializedString .= $this->serializeDocument($value);
            } else {
                // If the value is a simple type, append it
                $serializedString .= '"' . $value . '"';
            }
        }
        
        $binaryHash = hash('sha256', $serializedString);
        $hexHash = bin2hex($binaryHash);
        // dd($binaryHash);

        return $hexHash;
    }


    function serializeDocument($documentStructure) {
        if (!is_array($documentStructure)) {
            return '"' . $documentStructure . '"';
        }
        
        $serializedString = '';
        
        foreach ($documentStructure as $key => $value) {
            $keyUpper = strtoupper($key);
            
            if (!is_array($value) || array_values($value) !== $value) { // Not an indexed array
                $serializedString .= '"' . $keyUpper . '"';
                $serializedString .= $this->serializeDocument($value);
            } else { // Indexed array
                $serializedString .= '"' . $keyUpper . '"';
                foreach ($value as $arrayElement) {
                    $serializedString .= '"' . $keyUpper . '"';
                    $serializedString .= $this->serializeDocument($arrayElement);
                }
            }
        }
        
        return $serializedString;
    }
    
    function generateReceiptUUID($documentStructure) {
        $serializedText = $this->serializeDocument($documentStructure);
        return $hash = hash('sha256', $serializedText, false); // Generate SHA256 hash as binary
        return bin2hex($hash); // Convert to 64-character hexadecimal string

        
    }

    function jsonData(){
            $jsonString = '{
                "receipts": [
                    {
                        "header": {
                            "dateTimeIssued": "2025-03-06T00:34:00Z",
                            "receiptNumber": "#1014",
                            "uuid": "",
                            "currency": "EGP",
                            "exchangeRate": 0
                        },
                        "documentType": {
                            "receiptType": "S",
                            "typeVersion": "1.2"
                        },
                        "seller": {
                            "rin": "675170532",
                            "companyTradeName": "شركة الصوٝى"
                        },
                        "itemData": [
                            {
                                "internalCode": "880609",
                                "description": "Samsung A02",
                                "unitType": "EA",
                                "quantity": 35,
                                "unitPrice": 247.96000,
                                "taxableItems": [
                                    {
                                        "taxType": "T1",
                                        "amount": 1096.30360,
                                        "rate": 14
                                    }
                                ]
                            }
                        ],
                        "totalAmount": 8887.04360
                    }
                ]
            }';
            
            // Convert JSON string to PHP array
            $documentStructure = json_decode($jsonString, true);
            
            // Call the serialization function
            return $serializedOutput = $this->generateReceiptUUID($documentStructure);
            
            // Output the serialized string
            return $serializedOutput;
    }


    
    // Helper function to check if the value is a simple type
    function isSimpleValueType($value) {
        return !is_array($value);
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
        $tax_live = einvoice_settings('tax_live');
        $einvoice = Einvoice::find($invoiceId);
        if (!$einvoice) {
            return json_encode([
                'success' => 0,
                'message' => 'Invoice id invalid'
            ]);
        }

        $reciever = User::where('users.id', $einvoice->order->client_id)->join('countries', 'countries.id', 'users.country_id')
            ->select('users.*', 'countries.name_ar as country_name')->first();
        $client = new Client();
        $response_token = $this->AuthPos();
        if (!$reciever) {
            return json_encode(['success' => 0, 'message' => trans('admin.reciever-exists')]);
        }

        if (isset($response_token['access_token'])) {
            $access_token = $response_token['access_token'];
            $url = $tax_live == 0 ? einvoice_settings('apiBaseUrlPreprodEg') : einvoice_settings('apiBaseUrlProdEg');
            $receiptNumber = "00002";
            $branch = Branch::where('id', $einvoice->order->branch_id)->first();
            $documentType = [
                "receiptType" => "S",
                "typeVersion" => "1.2"
            ];
            $seller = [
                "rin" => einvoice_settings('tax_issuer_id'),
                "companyTradeName" => einvoice_settings('company_name'),
                "branchCode" => "0",
                "branchAddress" => [
                    "country" => $branch->country->code,
                    "governate" => $branch->governate,
                    "regionCity" => $branch->regionCity,
                    "street" => $branch->street,
                    "buildingNumber" => $branch->buildingNumber,
                    "postalCode" => "",
                    "floor" => "",
                    "room" => "",
                    "landmark" => "",
                    "additionalInformation" => ""
                ],
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
            foreach ($einvoice->order->orderDetails as $detail) {
                $netSale = round($detail->price_befor_tax / $detail->quantity, 2);
                $unitPrice = round($detail->price_befor_tax / $detail->quantity, 2);
                $totalSale = round($detail->price_befor_tax, 2);
                $total = round($detail->total_price_after_tax, 2);
                $internalCode = "dish_" . str_pad($detail->dish->code, 5, "0", STR_PAD_LEFT) . "_" . time();
                $data = [
                    "internalCode" => $internalCode,
                    "description" => $detail->dish->description_ar,
                    "itemType" => "EGS",
                    "itemCode" => "EG-" . einvoice_settings('tax_issuer_id') . str_pad($detail->dish->code, 5, "0", STR_PAD_LEFT),
                    "unitType" => "EA",
                    "quantity" => $detail->quantity,
                    "unitPrice" => $unitPrice,
                    "netSale" => $netSale,
                    "totalSale" => $totalSale,
                    "total" => $total,
                    "valueDifference" => 0,
                    "taxableItems" => [
                        [
                            "taxType" => "T1",
                            "amount" => round($detail->tax_value, 2),
                            "subType" => "V009",
                            "rate" => round(($detail->tax_value / $detail->price_befor_tax) * 100, 2)
                        ]
                    ]
                ];

                //dd($data);
                $TotalNetSale += $netSale;
                $TotalUnitPrice += $unitPrice;
                $TotalSales += $totalSale;
                $Totals += $total;
                $itemData[] = $data;
            }

            $header = [
                "dateTimeIssued" => gmdate("Y-m-d\TH:i:s\Z", time() - 3600),
                "receiptNumber" => $receiptNumber,
                "currency" => "EGP",
                "uuid"=>"79d79f838db2856eabf3b4d8e77df17f0fa43f1bcb08e59e56e770c7cafece2f",
                "previousUUID" => "",
                "referenceOldUUID" => "",
                "exchangeRate" => 0,
                "sOrderNameCode" => "sOrderNameCode",
                "orderdeliveryMode" => "",
                "grossWeight" => 0,
                "netWeight" => 0
            ];
            $data = [
                [
                    "header" => $header,
                    "documentType" => $documentType,
                    "seller" => $seller,
                    "buyer" => $buyer,
                    "itemData" => $itemData,
                    "totalSales" => $TotalSales,
                    "netAmount" => $TotalNetSale,
                    // "feesAmount" => round($einvoice->order->service_fees, 2),
                    "feesAmount" => 0,
                    "totalAmount" => $Totals,
                    "taxTotals" => [
                        [
                            "taxType" => "T1",
                            "amount" => round($einvoice->order->tax_value, 2)
                        ]
                    ],
                    "paymentMethod" => "C"
                ]
            ];

            //$InvData['receipts'] = $data;

            /*$receiptUuid1 = $this->serializeDocument($data[0]);*/
            $InvData['receipts'] = $data;
            //$receiptUuid1 = trim(generateReceiptUUID($InvData));
            return $receiptUuid1 = $this->generateReceiptUUID($data);
            
            // $data[0]['header']['uuid'] = $receiptUuid1;

            // return $data;

            // return $InvData;
            
            
            $urlappend = '/api/v1/receiptsubmissions';
            // try {
            ini_set('serialize_precision', -1);
            $response = $client->request('POST', $url . $urlappend, [
                'headers'  =>  [
                    'Authorization' => "Bearer {$access_token}",
                    'content-type' => 'application/json',
                    'accept' => 'application/ld+json'
                ],
                'body' => json_encode($InvData)
            ]);
            
            return $response = json_decode($response->getBody()->getContents(), true);

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
            //     // dd($exception);
            //     $response = json_decode($exception->getResponse()->getBody(true)->getContents(), true);
            //     if ($response) {
            //         helper_update_by_id(['error_msg' => $response['error']], $invoice->id, 'orders');
            //         return json_encode(['success' => 0, 'message' => $response['error']]);
            //     }
            //     return json_encode(['success' => 0, 'message' => 'Unknown Error']);
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
            'headers'  =>  [
                'posserial' => 'Test_SerialNo1', //Test_SerialNo1
                'pososversion' => 'IOS', //IOS
                'posmodelframework' => '1', //1
                'presharedkey' => 'C68FEC13-FE34-4839-89F9-ADB0D678CFE0' //
            ],
        ]);
        $response = json_decode($response->getBody()->getContents(), true);
        return $response;
    }
}
