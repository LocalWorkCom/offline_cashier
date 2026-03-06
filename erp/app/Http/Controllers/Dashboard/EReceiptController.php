<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Einvoice;
use App\Models\Order;
use App\Models\User;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;


class EReceiptController extends Controller
{

    /**
     *
     * submit einvoice to EGYPTIAN TAX  Without Sign
     *
     * @param int subscription invoice id
     * @return json api response
     *
     */
    public function SendPortal(Request $request)
    {
        $selectedInvoices = $request->test;
        $einvoices = explode(',', $selectedInvoices);

        $results = SendPortal($einvoices);

        foreach ($results as $result) {
            if ($result['status'] === 'error') {
                return Redirect::back()->withErrors($result['msg']);
            }
        }

        return Redirect::back()->with('success', 'Invoices sent successfully to portal.');
    }


    public function GetReceiptDetails($einvoice_id)
    {
        $result = GetReceiptDetails($einvoice_id);

        if (isset($result['error'])) {
            return Redirect::back()->withErrors($result['message']);
        }

        return Redirect::back()->with('success', $result['message'] ?? 'Details retrieved successfully.');
    }
}
