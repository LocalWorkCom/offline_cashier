<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CompanyProfileSetting;
use App\Models\ContactInformationSetting;
use App\Services\SettingsServices\ContactInformationSettingService;

class ContactInformationSettingController extends Controller
{
    protected $contactInformationSettingService;
    protected $checkToken;
    protected $lang;

    public function __construct(ContactInformationSettingService $contactInformationSettingService)
    {
        $this->contactInformationSettingService = $contactInformationSettingService;
        $this->checkToken = false;
        $this->lang = app()->getLocale();
    }

    public function index(Request $request)
    {
        $response = $this->contactInformationSettingService->index($request, $this->checkToken);
        // If it returns a query builder, execute the query
        $contactInformationSettings = $response->get();
        $CompanyProfileSetting = CompanyProfileSetting::all(); // Example
        return view('dashboard.contactInformationSetting.list', compact('CompanyProfileSetting', 'contactInformationSettings'));
    }
    public function create()
    {
        $ContactInformationSetting = CompanyProfileSetting::all(); // Example
        return view('dashboard.contactInformationSetting.add', compact('ContactInformationSetting'));
    }

    public function store(Request $request)
    {
        try {
            $result = $this->contactInformationSettingService->store($request, $this->checkToken);

            // Check if result is an array (error response from service)
            if (is_array($result) && isset($result['status']) && !$result['status']) {
                return redirect()->back()->withErrors($result['errorData'] ?? [])->withInput();
            }

            // Check if result is a BusinessActivity object (success)
            if ($result instanceof \App\Models\ContactInformationSetting) {
                return redirect()->route('contact_information_setting.list')->with('message', 'contactInformationSettingService created successfully');
            }

            // If it's still a response object (fallback)
            if ($result instanceof \Illuminate\Http\JsonResponse) {
                $responseData = $result->getData();
                if (!$responseData->status && isset($responseData->data)) {
                    return redirect()->back()->withErrors($responseData->data)->withInput();
                }
                return redirect()->route('contact_information_setting.list')->with('message', $responseData->message);
            }

            // Default success
            return redirect()->route('contact_information_setting.list')->with('message', 'contactInformationSettingService created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }
    public function show($id)
    {
        $contactInformationSetting = ContactInformationSetting::findOrFail($id);
        $companyProfileSetting = CompanyProfileSetting::all();
        return view('dashboard.contactInformationSetting.show', compact('companyProfileSetting', 'contactInformationSetting'));
    }

    public function edit($id)
    {
        $contactInformationSetting = ContactInformationSetting::findOrFail($id);
        $companyProfileSetting = CompanyProfileSetting::all();

        return view('dashboard.contactInformationSetting.edit', compact('companyProfileSetting', 'contactInformationSetting', 'id'));
    }

    public function update(Request $request, $id)
    {
        try {
            $result = $this->contactInformationSettingService->update($request, $id, $this->checkToken);

            // Check if result is a JSON response (from service)
            if ($result instanceof \Illuminate\Http\JsonResponse) {
                $responseData = $result->getData();

                if (!$responseData->status && isset($responseData->data)) {
                    return redirect()->back()->withErrors($responseData->data)->withInput();
                }

                return redirect()->route('contact_information_setting.list')->with('message', $responseData->message ?? 'contact_information_setting updated successfully');
            }

            // Check if result is an array (error response from service)
            if (is_array($result) && isset($result['status']) && !$result['status']) {
                return redirect()->back()->withErrors($result['errorData'] ?? $result['data'] ?? [])->withInput();
            }

            // Check if result is a BusinessActivity object (success)
            if ($result instanceof \App\Models\ContactInformationSetting) {
                return redirect()->route('contact_information_setting.list')->with('message', 'contact_information_setting updated successfully');
            }

            // Default success
            return redirect()->route('contact_information_setting.list')->with('message', 'contact_information_setting updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }
    public function delete($id)
    {
        $contactInformationSetting = ContactInformationSetting::findOrFail($id);
        $contactInformationSetting->delete();

        return redirect()->route('contact_information_setting.list')->with('message', 'Contact information deleted successfully.');
    }
}
