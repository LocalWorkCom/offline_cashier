<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CompanyProfileSetting;
use App\Models\SocialMediaInformationSetting;
use App\Services\SettingsServices\SocialMediaInformationSettingService;

class SocialMediaInformationSettingController extends Controller
{
    protected $socialMediaInformationSettingService;
    protected $checkToken;
    protected $lang;

    public function __construct(SocialMediaInformationSettingService $socialMediaInformationSettingService)
    {
        $this->socialMediaInformationSettingService = $socialMediaInformationSettingService;
        $this->checkToken = false;
        $this->lang = app()->getLocale();
    }

    public function index(Request $request)
    {
        $response = $this->socialMediaInformationSettingService->index($request, $this->checkToken);
        // If it returns a query builder, execute the query
        $socialMediaInformationSetting = $response->get();
        $CompanyProfileSetting = CompanyProfileSetting::all(); // Example
        return view('dashboard.socialMediaInformationSetting.list', compact('CompanyProfileSetting', 'socialMediaInformationSetting'));
    }

    public function create()
    {
        $socialMediaInformationSetting = CompanyProfileSetting::all(); // Example
        return view('dashboard.socialMediaInformationSetting.add', compact('socialMediaInformationSetting'));
    }

    public function store(Request $request)
    {
        try {
            $result = $this->socialMediaInformationSettingService->store($request, $this->checkToken);

            // Check if result is an array (error response from service)
            if (is_array($result) && isset($result['status']) && !$result['status']) {
                return redirect()->back()->withErrors($result['errorData'] ?? [])->withInput();
            }

            // Check if result is a BusinessActivity object (success)
            if ($result instanceof \App\Models\SocialMediaInformationSetting) {
                return redirect()->route('social_media_information_setting.list')->with('message', 'social_media_information_setting created successfully');
            }

            // If it's still a response object (fallback)
            if ($result instanceof \Illuminate\Http\JsonResponse) {
                $responseData = $result->getData();
                if (!$responseData->status && isset($responseData->data)) {
                    return redirect()->back()->withErrors($responseData->data)->withInput();
                }
                return redirect()->route('social_media_information_setting.list')->with('message', $responseData->message);
            }

            // Default success
            return redirect()->route('social_media_information_setting.list')->with('message', 'social_media_information_setting created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        $socialMediaInformationSetting = SocialMediaInformationSetting::findOrFail($id);
        $companyProfileSetting = CompanyProfileSetting::all();
        return view('dashboard.socialMediaInformationSetting.show', compact('companyProfileSetting', 'socialMediaInformationSetting'));
    }

    public function edit($id)
    {
        $socialMediaInformationSetting = SocialMediaInformationSetting::findOrFail($id);
        $companyProfileSetting = CompanyProfileSetting::all();

        return view('dashboard.socialMediaInformationSetting.edit', compact('companyProfileSetting', 'socialMediaInformationSetting', 'id'));
    }

    public function update(Request $request, $id)
    {
        try {
            $result = $this->socialMediaInformationSettingService->update($request, $id, $this->checkToken);

            // Check if result is a JSON response (from service)
            if ($result instanceof \Illuminate\Http\JsonResponse) {
                $responseData = $result->getData();

                if (!$responseData->status && isset($responseData->data)) {
                    return redirect()->back()->withErrors($responseData->data)->withInput();
                }

                return redirect()->route('social_media_information_setting.list')->with('message', $responseData->message ?? 'social_media_information_setting updated successfully');
            }

            // Check if result is an array (error response from service)
            if (is_array($result) && isset($result['status']) && !$result['status']) {
                return redirect()->back()->withErrors($result['errorData'] ?? $result['data'] ?? [])->withInput();
            }

            // Check if result is a BusinessActivity object (success)
            if ($result instanceof \App\Models\SocialMediaInformationSetting) {
                return redirect()->route('social_media_information_setting.list')->with('message', 'social_media_information_setting updated successfully');
            }

            // Default success
            return redirect()->route('social_media_information_setting.list')->with('message', 'social_media_information_setting updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])->withInput();
        }
    }

    public function delete($id)
    {
        $socialMediaInformationSetting = SocialMediaInformationSetting::findOrFail($id);
        $socialMediaInformationSetting->delete();

        return redirect()->route('social_media_information_setting.list')->with('message', 'Contact information deleted successfully.');
    }
}
