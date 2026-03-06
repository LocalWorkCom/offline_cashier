<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Models\BusinessActivity;
use App\Http\Controllers\Controller;
use App\Services\SettingsServices\BusinessActivityService;

class BusinessActivityController extends Controller
{
    protected $BusinessActivityService;
    protected $checkToken;
    protected $lang;

    public function __construct(BusinessActivityService  $BusinessActivityService)
    {
        $this->BusinessActivityService  = $BusinessActivityService;
        $this->checkToken = false;
        $this->lang =  app()->getLocale();
    }

    public function index(Request $request)
    {
        $response = $this->BusinessActivityService->index($request, $this->checkToken);
        // If it returns a query builder, execute the query
        $businessActivity = $response->get();

        return view('dashboard.businessActivity.list', compact('businessActivity'));
    }

    public function create()
    {
        $businessActivity = BusinessActivity::where('is_active', 1)->whereNull('deleted_at')->get(); // Example

        return view('dashboard.businessActivity.add', compact('businessActivity'));
    }

    public function store(Request $request)
    {
        try {
            $result = $this->BusinessActivityService->store($request, $this->checkToken);

            // Check if result is an array (error response from service)
            if (is_array($result) && isset($result['status']) && !$result['status']) {
                return redirect()->back()->withErrors($result['errorData'] ?? [])->withInput();
            }

            // Check if result is a BusinessActivity object (success)
            if ($result instanceof \App\Models\BusinessActivity) {
                return redirect()->route('business_activity.list')->with('message', 'Business activity created successfully');
            }

            // If it's still a response object (fallback)
            if ($result instanceof \Illuminate\Http\JsonResponse) {
                $responseData = $result->getData();
                if (!$responseData->status && isset($responseData->data)) {
                    return redirect()->back()->withErrors($responseData->data)->withInput();
                }
                return redirect()->route('business_activity.list')->with('message', $responseData->message);
            }

            // Default success
            return redirect()->route('business_activity.list')->with('message', 'Business activity created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        $businessActivity = BusinessActivity::findOrFail($id);

        return view('dashboard.businessActivity.show', compact('businessActivity'));
    }

    public function edit($id)
    {
        $businessActivity = BusinessActivity::findOrFail($id);

        return view('dashboard.businessActivity.edit', compact('businessActivity', 'id'));
    }

    public function update(Request $request, $id)
    {
        try {
            $result = $this->BusinessActivityService->update($request, $id, $this->checkToken);

            // Check if result is a JSON response (from service)
            if ($result instanceof \Illuminate\Http\JsonResponse) {
                $responseData = $result->getData();

                if (!$responseData->status && isset($responseData->data)) {
                    return redirect()->back()->withErrors($responseData->data)->withInput();
                }

                return redirect()->route('business_activity.list')->with('message', $responseData->message ?? 'Business activity updated successfully');
            }

            // Check if result is an array (error response from service)
            if (is_array($result) && isset($result['status']) && !$result['status']) {
                return redirect()->back()->withErrors($result['errorData'] ?? $result['data'] ?? [])->withInput();
            }

            // Check if result is a BusinessActivity object (success)
            if ($result instanceof \App\Models\BusinessActivity) {
                return redirect()->route('business_activity.list')->with('message', 'Business activity updated successfully');
            }

            // Default success
            return redirect()->route('business_activity.list')->with('message', 'Business activity updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }

    public function delete(Request $request, $id)
    {
        $response = $this->BusinessActivityService->destroy($request, $id, $this->checkToken);
        $responseData = $response->original;

        return redirect()->route('business_activity.list')->with('message', $responseData['message']);
    }
}
