<?php

namespace App\Http\Controllers\Api\CashierAPIs;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Branch;
use App\Models\BranchRegion;
use App\Models\ClientAddress;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\User;
use App\Services\AddressServices\BranchSiteService;
use App\Services\AddressServices\LocationDataService;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\GoogleMapsService;
use Illuminate\Support\Facades\App;

class addressController extends Controller
{
    protected $googleMapsService;
    protected $locationService;

    protected $branchSiteService;

    public function __construct(GoogleMapsService $googleMapsService, BranchSiteService $branchSiteService, LocationDataService $locationService)
    {
        $this->googleMapsService = $googleMapsService;
        $this->locationService = $locationService;

        $this->branchSiteService = $branchSiteService;
    }

    public function getAreasByBranch(Request $request, $branchId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $areas = $this->branchSiteService->getAreasByBranch($request, $branchId);
        if ($areas) {
            return ResponseWithSuccessData($lang, $areas, 1);
        } else {
            return RespondWithBadRequest($request->header('lang', 'ar'), 22);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $employee = auth('employee')->user();

        $result = $this->branchSiteService->storeAddress($request, $employee);

        return  $result;
    }


    public function listHotel(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $hotels = $this->locationService->all($lang);

        if ($hotels->isNotEmpty()) {
            return ResponseWithSuccessData($lang, $hotels, 1);
        }

        // return RespondWithBadRequest($lang, 22);
        return ResponseWithSuccessData($lang, $hotels, 1);
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getAreas(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $areas = Area::where('city_id', $id)->select('id', 'name_ar', 'name_en')->get();
        if ($areas) {
            return ResponseWithSuccessData($lang, $areas, 1);
        } else {
            return RespondWithBadRequest($request->header('lang', 'ar'), 22);
        }
    }
    public function getAllAreas(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $areas = Area::select('id', 'name_ar', 'name_en');
        $areas = paginateOrGetAll($areas, $request, null, null);
        if ($areas['data']) {
            return ResponseWithSuccessDataPaginated($lang, $areas, 1);
        } else {
            return RespondWithBadRequest($request->header('lang', 'ar'), 22);
        }
    }

    public function check_available_areas(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $validator = Validator::make($request->all(), [
            'city_id' => 'required|exists:cities,id',
            'region_id' => 'required|exists:areas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ], 400);
        }
        $branchRegions = BranchRegion::whereNull('deleted_at')->get();
        foreach ($branchRegions as $region) {
            if ($region->region_id == $request->region_id) {

                $massege = $lang === "ar" ? "هذه المنطقه مستخدمه من فرع اخر وسوف يتم مسحها من الفرع عند الحفظ" : "This area is used by another branch and will be deleted from the branch upon saving.";

                return ResponseWithSuccessData($lang, $massege, 1);
            }
        }
        $massege = $lang === "ar" ? "المنطقه متاحه" : "region avaliable.";

        return ResponseWithSuccessData($lang, $massege, 1);
    }
}
