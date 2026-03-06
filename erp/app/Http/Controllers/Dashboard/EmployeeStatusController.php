<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\EmployeeStatus;
use App\Services\HR_Services\EmployeeStatusService;
use Illuminate\Http\Request;

class EmployeeStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    protected $EmployeeStatusService;
    protected $checkToken;
    protected $lang;

    public function __construct(EmployeeStatusService $EmployeeStatusService)
    {
        $this->EmployeeStatusService = $EmployeeStatusService;
        $this->checkToken = false;
        $this->lang =  app()->getLocale();
    }

    public function index(Request $request)
    {
        // Get the query builder from service
        $query = $this->EmployeeStatusService->index($request, $this->checkToken);

        // If the service returns a response object (like in error case)
        if ($query instanceof \Illuminate\Http\Response) {
            return $query; // Return the error response directly
        }

        // Execute the query to get the results
        $EmployeeStatus = $query->get();
        $employeeStatusCount = EmployeeStatus::count();

        return view('dashboard.employee_status.list', compact('EmployeeStatus', 'employeeStatusCount'));
    }

    
    public function store(Request $request)
    {
        $response = $this->EmployeeStatusService->store($request, $this->checkToken);

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
            return redirect('dashboard/employee_status')
                ->withErrors($validationErrors)
                ->withInput();
        }

        $message = $responseData['message'] ?? 'Something went wrong';
        return redirect('dashboard/employee_status')->with('message', $message);
    }

    public function update(Request $request, $id)
    {
        $response = $this->EmployeeStatusService->update($request, $id, $this->checkToken);

        // Normalize response to array
        if ($response instanceof \Illuminate\Http\Response || $response instanceof \Illuminate\Http\JsonResponse) {
            $responseData = $response->getOriginalContent();
        } else {
            $responseData = $response;
        }

        // Handle validation errors
        if (isset($responseData['status']) && !$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect('dashboard/employee_status')
                ->withErrors($validationErrors)
                ->withInput();
        }

        // Success / fallback message
        $message = $responseData['message'] ?? 'Something went wrong';
        return redirect('dashboard/employee_status')->with('message', $message);
    }

    public function delete(Request $request, $id)
    {
        $response = $this->EmployeeStatusService->delete($request, $id, $this->checkToken);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/employee_status')->with('message', $message);
    }
}
