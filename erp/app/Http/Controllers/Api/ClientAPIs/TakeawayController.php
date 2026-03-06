<?php

namespace App\Http\Controllers\Api\ClientAPIs;

use App\Http\Controllers\Controller;
use App\Services\AddressServices\BranchSiteService;
use Illuminate\Http\Request;

class TakeawayController extends Controller
{
    // Service for branch-related operations
    protected $branchService;

    // Current language for responses
    private $lang;

    /**
     * TakeawayController constructor.
     * Sets the language and injects the branch service.
     */
    public function __construct(BranchSiteService $branchService, Request $request)
    {
        // Get language from request header, default to Arabic
        $this->lang = $request->header('lang', 'ar');
        $this->branchService = $branchService;
    }

    /**
     * Check availability for a branch at a specific date/time.
     * @param Request $request
     * @return mixed
     */
    public function checkAvailability(Request $request)
    {
        $branchId = $request->branch_id;
        $dateString = $request->date;

        // Call the service method to check availability
        $result = $this->branchService->checkAvailability($branchId, $dateString);
        return $result;
    }

    /**
     * Check if the branch can accept more orders at the requested time.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkOrderCapacity(Request $request)
    {
        $response = $this->branchService->checkOrderCapacity($request, $this->lang);

        // If not available, return an error response
        if (!$response['available']) {
            return respondError(400, 400, 'this time has been fully booked');
        }
        // Otherwise, return success with data
        return ResponseWithSuccessData($this->lang, $response, 1);
    }

    /**
     * Search for branches based on request filters.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchBranches(Request $request)
    {
        $branch = $this->branchService->searchBranches($request->all(), $this->lang);

        // If no branch found, return an error response
        if (!$branch) {
            return respondError(400, 400, 'No branches found');
        }
        // Hide sensitive fields before returning
        $branch = $branch?->makeHidden(['name_site', 'address_site', 'is_branch_open']);
        return ResponseWithSuccessData($this->lang, $branch, 1);
    }
}
