<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\EthnicBackground;
use App\Services\HR_Services\EthnicBackgroundService;
use Illuminate\Http\Request;

class EthnicBackgroundController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    protected $EthnicBackgroundService;
    protected $checkToken;
    protected $lang;

    public function __construct(EthnicBackgroundService $EthnicBackgroundService)
    {
        $this->EthnicBackgroundService = $EthnicBackgroundService;
        $this->checkToken = false;
        $this->lang =  app()->getLocale();
    }
    public function index(Request $request)
    {
        // Get the query builder from service
        $query = $this->EthnicBackgroundService->index($request, $this->checkToken);

        // If the service returns a response object (like in error case)
        if ($query instanceof \Illuminate\Http\Response) {
            return $query; // Return the error response directly
        }

        // Execute the query to get the results
        $EthnicBackgrounds = $query->get();
        $ethnicBackgroundsCount = EthnicBackground::count();

        return view('dashboard.ethnic_backgrounds.list', compact('EthnicBackgrounds', 'ethnicBackgroundsCount'));
    }

    public function store(Request $request)
    {
        $response = $this->EthnicBackgroundService->store($request, $this->checkToken);

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
            return redirect('dashboard/ethnic_backgrounds')
                ->withErrors($validationErrors)
                ->withInput();
        }

        $message = $responseData['message'] ?? 'Something went wrong';
        return redirect('dashboard/ethnic_backgrounds')->with('message', $message);
    }

    public function update(Request $request, $id)
    {
        $response = $this->EthnicBackgroundService->update($request, $id, $this->checkToken);

        // Normalize response to array
        if ($response instanceof \Illuminate\Http\Response || $response instanceof \Illuminate\Http\JsonResponse) {
            $responseData = $response->getOriginalContent();
        } else {
            $responseData = $response;
        }

        // Handle validation errors
        if (isset($responseData['status']) && !$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect('dashboard/ethnic_backgrounds')
                ->withErrors($validationErrors)
                ->withInput();
        }

        // Success / fallback message
        $message = $responseData['message'] ?? 'Something went wrong';
        return redirect('dashboard/ethnic_backgrounds')->with('message', $message);
    }

    public function delete(Request $request, $id)
    {
        $response = $this->EthnicBackgroundService->delete($request, $id, $this->checkToken);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/ethnic_backgrounds')->with('message', $message);
    }
}
