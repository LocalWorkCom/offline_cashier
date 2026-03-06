<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Models\BusinessActivity;
use App\Http\Controllers\Controller;
use App\Models\CompanyProfileSetting;
use App\Services\SettingsServices\CompanyProfileSettingService;

class CompanyProfileSettingController extends Controller
{
    protected $CompanyProfileSettingService;
    protected $checkToken;
    protected $lang;

    public function __construct(CompanyProfileSettingService  $CompanyProfileSettingService)
    {
        $this->CompanyProfileSettingService  = $CompanyProfileSettingService;
        $this->checkToken = false;
        $this->lang =  app()->getLocale();
    }

    public function index(Request $request)
    {
        $response = $this->CompanyProfileSettingService->index($request, $this->checkToken);
        // If it returns a query builder, execute the query
        $companyProfileSettings = $response->get();

        return view('dashboard.companyProfileSetting.list', compact('companyProfileSettings'));
    }

    public function create()
    {
        $companyProfileSetting = BusinessActivity::where('is_active', 1)->whereNull('deleted_at')->get(); // Example

        return view('dashboard.companyProfileSetting.add', compact('companyProfileSetting'));
    }


    public function store(Request $request)
    {
        try {
            $result = $this->CompanyProfileSettingService->store($request, $this->checkToken);

            // Check if result is an array (error response from service)
            if (is_array($result) && isset($result['status']) && !$result['status']) {
                return redirect()->back()->withErrors($result['errorData'] ?? [])->withInput();
            }

            // Check if result is a BusinessActivity object (success)
            if ($result instanceof \App\Models\BusinessActivity) {
                return redirect()->route('company_profile_setting.list')->with('message', 'company_profile_setting created successfully');
            }

            // If it's still a response object (fallback)
            if ($result instanceof \Illuminate\Http\JsonResponse) {
                $responseData = $result->getData();
                if (!$responseData->status && isset($responseData->data)) {
                    return redirect()->back()->withErrors($responseData->data)->withInput();
                }
                return redirect()->route('company_profile_setting.list')->with('message', $responseData->message);
            }

            // Default success
            return redirect()->route('company_profile_setting.list')->with('message', 'company_profile_setting created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }
    public function show($id)
    {
        $companyProfileSetting = CompanyProfileSetting::findOrFail($id);
        $companyProfileSettings = BusinessActivity::all(); // Example

        return view('dashboard.companyProfileSetting.show', compact('companyProfileSetting', 'companyProfileSettings'));
    }

    public function edit($id)
    {
        $companyProfileSetting = CompanyProfileSetting::findOrFail($id);
        $companyProfileSettings = BusinessActivity::where('is_active', 1)->whereNull('deleted_at')->get(); // Get all business activities

        return view('dashboard.companyProfileSetting.edit', compact('companyProfileSetting', 'companyProfileSettings', 'id'));
    }

    public function update(Request $request, $id)
    {
        try {
            $result = $this->CompanyProfileSettingService->update($request, $id, $this->checkToken);

            // Check if result is a JSON response (from service)
            if ($result instanceof \Illuminate\Http\JsonResponse) {
                $responseData = $result->getData();

                if (!$responseData->status && isset($responseData->data)) {
                    return redirect()->back()->withErrors($responseData->data)->withInput();
                }

                return redirect()->route('company_profile_setting.list')->with('message', $responseData->message ?? 'company_profile_setting updated successfully');
            }

            // Check if result is an array (error response from service)
            if (is_array($result) && isset($result['status']) && !$result['status']) {
                return redirect()->back()->withErrors($result['errorData'] ?? $result['data'] ?? [])->withInput();
            }

            // Check if result is a BusinessActivity object (success)
            if ($result instanceof \App\Models\BusinessActivity) {
                return redirect()->route('company_profile_setting.list')->with('message', 'company_profile_setting updated successfully');
            }

            // Default success
            return redirect()->route('company_profile_setting.list')->with('message', 'company_profile_setting updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }
    public function delete(Request $request, $id)
    {
        $response = $this->CompanyProfileSettingService->destroy($request, $id, $this->checkToken);
        $responseData = $response->original;

        return redirect()->route('company_profile_setting.list')->with('message', $responseData['message']);
    }
}
