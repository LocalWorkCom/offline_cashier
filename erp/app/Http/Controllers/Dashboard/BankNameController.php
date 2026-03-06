<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BankName;
use App\Services\SettingsServices\BankNameService;
use Illuminate\Http\Request;

class BankNameController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    protected $BankNameService;
    protected $checkToken;
    protected $lang;

    public function __construct(BankNameService $BankNameService)
    {
        $this->BankNameService = $BankNameService;
        $this->checkToken = false;
        $this->lang =  app()->getLocale();
    }

    public function index(Request $request)
    {
        // Get the query builder from service
        $bankNamesQuery = $this->BankNameService->index($request, $this->checkToken);

        // If the service returns a response object (like in error case)
        if ($bankNamesQuery instanceof \Illuminate\Http\Response) {
            return $bankNamesQuery; // Return the error response directly
        }

        // Execute the query to get the results
        $bankNames = $bankNamesQuery->get();

        $TotalBankNames = BankName::count();

        return view('dashboard.bank_names.list', [
            'bank_names' => $bankNames,
            'TotalBankNames' => $TotalBankNames,
        ]);
    }


    public function store(Request $request)
    {
        $response = $this->BankNameService->store($request, $this->checkToken);

        // If it's a Response object
        if ($response instanceof \Illuminate\Http\Response) {
            $responseData = $response->getOriginalContent();
        } else {
            // Already array or object
            $responseData = $response;
        }

        // Safely check response structure
        if (isset($responseData['status']) && !$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect('dashboard/bank_names')
                ->withErrors($validationErrors)
                ->withInput();
        }

        $message = $responseData['message'] ?? 'Something went wrong';
        return redirect('dashboard/bank_names')->with('message', $message);
    }


    public function update(Request $request, $id)
    {
        $response = $this->BankNameService->update($request, $id, $this->checkToken);

        // Normalize response to array
        if ($response instanceof \Illuminate\Http\Response || $response instanceof \Illuminate\Http\JsonResponse) {
            $responseData = $response->getOriginalContent();
        } else {
            $responseData = $response;
        }

        // Handle validation errors
        if (isset($responseData['status']) && !$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect('dashboard/bank_names')
                ->withErrors($validationErrors)
                ->withInput();
        }

        // Success / fallback message
        $message = $responseData['message'] ?? 'Something went wrong';
        return redirect('dashboard/bank_names')->with('message', $message);
    }

    public function delete(Request $request, $id)
    {
        $response = $this->BankNameService->delete($request, $id, $this->checkToken);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/bank_names')->with('message', $message);
    }
}
