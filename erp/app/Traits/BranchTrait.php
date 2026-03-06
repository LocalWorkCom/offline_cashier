<?php

namespace App\Traits;

use App\Models\Branch;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

trait BranchTrait
{
    public function managerName($id)
    {
        $manager = Employee::find($id);
        return $manager ? $manager->first_name . ' ' . $manager->last_name : null;
    }

    public function listBranchAndNearFilter(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'latitute' => ['nullable', 'numeric', 'regex:/^-?\d+(\.\d+)?$/', 'required_without:country_id,city_id,area_id'],
            'longitute' => ['nullable', 'numeric', 'regex:/^-?\d+(\.\d+)?$/', 'required_without:country_id,city_id,area_id'],
            'country_id' => ['nullable', 'exists:countries,id', 'required_without:latitute,longitute'],
            'city_id' => ['nullable', 'numeric', 'exists:cities,id', 'required_without:latitute,longitute'],
            'area_id' => ['nullable', 'numeric', 'exists:areas,id', 'required_without:latitute,longitute'],
        ], [
            'latitute.required_without' => __('validation.latitute.required_without'),
            'latitute.numeric' => __('validation.latitute.numeric'),
            'latitute.regex' => __('validation.latitute.regex'),
            'longitute.required_without' => __('validation.longitute.required_without'),
            'longitute.numeric' => __('validation.longitute.numeric'),
            'longitute.regex' => __('validation.longitute.regex'),
            'country_id.exists' => __('validation.country_id.exists'),
            'city_id.exists' => __('validation.city_id.exists'),
            'area_id.exists' => __('validation.area_id.exists'),
            'country_id.required_without' => __('validation.country_id.required_without'),
            'city_id.required_without' => __('validation.city_id.required_without'),
            'area_id.required_without' => __('validation.area_id.required_without'),
        ]);

        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }

        $userLat = $request->query('latitute');
        $userLon = $request->query('longitute');
        $countryId = $request->query('country_id');
        $cityId = $request->query('city_id');
        $areaId = $request->query('area_id');

        $query = Branch::where('is_active', 1);

        if ($userLat && $userLon) {
            $query->select('*')
                ->selectRaw("(6371 * acos(cos(radians($userLat))
                      * cos(radians(latitute))
                      * cos(radians(longitute) - radians($userLon))
                      + sin(radians($userLat))
                      * sin(radians(latitute)))) AS distance")
                ->whereNotNull('latitute')
                ->whereNotNull('longitute')
                ->orderBy('distance', 'asc');
        } elseif ($countryId && $cityId && $areaId) {

            $query->where('country_id', $countryId)
                ->where('city_id', $cityId)
                ->where('area_id', $areaId)
                ->orWhereHas('branchRegions', function ($q) use ($areaId) {
                    $q->where('region_id', $areaId);
                });
        }

        if ($request->has('branchesStatus')) {
            $query->where('is_open', 1);
        }

        $branches = $query->get();

        if ($branches->isEmpty()) {
            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => $lang == 'en' ? 'No branches found' : 'لا يوجد فروع',
                'data' => null
            ]);
        }

        $branch = $this->prepareBranchData($branches->first(), $lang);
        return ResponseWithSuccessData($lang, ['branch' => $branch], 1);
    }

    public function listBranchAndNear(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $request->validate([
            'latitute' => ['numeric', 'regex:/^-?\d+(\.\d+)?$/'],
            'longitute' => ['numeric', 'regex:/^-?\d+(\.\d+)?$/'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'area_id' => ['nullable', 'exists:areas,id'],
        ], [
            'latitute.required' => __('validation.latitute.required'),
            'latitute.numeric' => __('validation.latitute.numeric'),
            'latitute.regex' => __('validation.latitute.regex'),
            'longitute.required' => __('validation.longitute.required'),
            'longitute.numeric' => __('validation.longitute.numeric'),
            'longitute.regex' => __('validation.longitute.regex'),
            'country_id.exists' => __('validation.country_id.exists'),
            'city_id.exists' => __('validation.city_id.exists'),
            'area_id.exists' => __('validation.area_id.exists'),
        ]);

        $userLat = $request->query('latitute');
        $userLon = $request->query('longitute');
        $all = $request->query('all', 1);
        $searchName = $request->query('name');
        $countryId = $request->query('country_id');
        $cityId = $request->query('city_id');
        $areaId = $request->query('area_id');
        $currentDay = Carbon::now()->dayOfWeek;

        $branchesQuery = $this->buildBranchesQuery($lang, $countryId, $cityId, $areaId, $searchName);
        $get_all_branches = $branchesQuery->get()->map(function ($branch) use ($currentDay) {
            return $this->prepareBranchData($branch);
        });

        $get_all_branches->makeHidden(['name_site', 'address_site']);
        $branches = ($all == 1) ? $get_all_branches : null;

        $data = $this->prepareResponseData($userLat, $userLon, $all, $branches, $currentDay);
        return ResponseWithSuccessData($lang, $data, 1);
    }

    private function buildBranchesQuery($lang, $countryId, $cityId, $areaId, $searchName)
    {
        $nameColumn = ($lang === 'ar') ? 'branches.name_ar' : 'branches.name_en';

        $query = Branch::query()
            ->whereNull('branches.deleted_at')
            ->where('branches.is_active', 1)
            ->join('countries', 'branches.country_id', '=', 'countries.id')
            ->select('branches.*', 'countries.currency_symbol');

        if ($countryId && $cityId && $areaId) {
            $query->where('branches.country_id', $countryId)
                ->where('branches.city_id', $cityId)
                ->where('branches.area_id', $areaId);
        }

        if ($searchName) {
            $query->where($nameColumn, 'like', "%{$searchName}%");
        }

        return $query;
    }

    private function prepareBranchData($branch, $lang = null)
    {
        $currentDay = Carbon::now()->dayOfWeek;
        // Unset existing hours first
        unset($branch->opening_hour);
        unset($branch->closing_hour);
        $branchTime = DB::table('branch_times')
            ->where('branch_id', $branch->id)
            ->where('day', $currentDay)
            ->where('is_active', 1)
            ->where('deleted_at' , null)
            ->first();

        $services = [];
        if ($branch->is_delivery) $services[] = __("header.deliveryTo");
        if ($branch->is_takeaway) $services[] = __("header.pickup");
        if ($branch->is_table_reservation) $services[] = __("header.reservation");

        $branch->services = implode(' - ', $services);
        $branch->working_times = getBranchWorkingHours($branch->id);
        $branch->opening_hour = $branchTime->opening_hour ?? null;
        $branch->closing_hour = $branchTime->closing_hour ?? null;
        $branch->manager_name = $this->managerName($branch->employee_id);
        $branch->floor_partitions = getBranchFloorPartitions($branch->id) ?? null;

        $booleanFields = [
            'is_default',
            'tax_apply',
            'is_open',
            'is_delivery',
            'is_takeaway',
            'is_table_reservation',
            'is_active',
            'has_kids_area',
            'tax_application',
            'coupon_application',
            'is_live'
        ];

        foreach ($booleanFields as $field) {
            $branch->$field = (bool)$branch->$field;
        }

        $branch->makeHidden(['name_site', 'address_site']);
        return $branch;
    }

    private function prepareResponseData($userLat, $userLon, $all, $branches, $currentDay)
    {
        $data = [];

        if ($userLat && $userLon) {
            $nearestBranch = getNearestBranch($userLat, $userLon);
            $data['branch'] = $nearestBranch ? $this->prepareNearestBranchData($nearestBranch, $currentDay) : $this->getDefaultBranchData();
        } else {
            $data['branch'] = $this->getDefaultBranchData();
        }

        $data['branches'] = $branches ?? [];
        return $data;
    }

    private function prepareNearestBranchData($branch, $currentDay)
    {
        $branch->load('country');
        // Unset existing hours first
        unset($branch->opening_hour);
        unset($branch->closing_hour);
        $branchTime = DB::table('branch_times')
            ->where('branch_id', $branch->id)
            ->where('day', $currentDay)
            ->where('is_active', 1)
            ->where('deleted_at' , null)
            ->first();

        $branch->is_delivery = (bool)$branch->is_delivery;
        $branch->is_default = (bool)$branch->is_default;
        $branch->tax_apply = (bool)$branch->tax_apply;
        $branch->tax_application = (bool)$branch->tax_application;
        $branch->coupon_application = (bool)$branch->coupon_application;
        $branch->has_kids_area = (bool)$branch->has_kids_area;
        $branch->is_live = (bool)$branch->is_live;
        $branch->is_table_reservation = (bool)$branch->is_table_reservation;
        $branch->is_takeaway = (bool)$branch->is_takeaway;
        $branch->opening_hour = $branchTime->opening_hour ?? null;
        $branch->closing_hour = $branchTime->closing_hour ?? null;
        $branch->manager_name = $this->managerName($branch->employee_id);
        $branch->currency_symbol = $branch->country->currency_symbol ?? null;
        $branch->floor_partitions = getBranchFloorPartitions($branch->id) ?? null;
        $branch->makeHidden(['name_site', 'address_site']);

        return $branch;
    }

    private function getDefaultBranchData()
    {
        $defaultBranchId = getDefaultBranch();
        $defaultBranch = Branch::join('countries', 'branches.country_id', '=', 'countries.id')
            ->select('branches.*', 'countries.currency_symbol')
            ->find($defaultBranchId);

        if (!$defaultBranch) {
            return null;
        }

        $currentDay = Carbon::now()->dayOfWeek;

        // Unset existing hours first
        unset($defaultBranch->opening_hour);
        unset($defaultBranch->closing_hour);
        $branchTime = DB::table('branch_times')
            ->where('branch_id', $defaultBranch->id)
            ->where('day', $currentDay)
            ->where('is_active', 1)
            ->where('deleted_at' , null)
            ->first();

        $defaultBranch->is_delivery = (bool)$defaultBranch->is_delivery;
        $defaultBranch->is_default = (bool)$defaultBranch->is_default;
        $defaultBranch->tax_apply = (bool)$defaultBranch->tax_apply;
        $defaultBranch->tax_application = (bool)$defaultBranch->tax_application;
        $defaultBranch->coupon_application = (bool)$defaultBranch->coupon_application;
        $defaultBranch->has_kids_area = (bool)$defaultBranch->has_kids_area;
        $defaultBranch->is_live = (bool)$defaultBranch->is_live;
        $defaultBranch->is_table_reservation = (bool)$defaultBranch->is_table_reservation;
        $defaultBranch->is_takeaway = (bool)$defaultBranch->is_takeaway;
        $defaultBranch->opening_hour = $branchTime->opening_hour ?? null;
        $defaultBranch->closing_hour = $branchTime->closing_hour ?? null;
        $defaultBranch->manager_name = $this->managerName($defaultBranch->employee_id);
        $defaultBranch->floor_partitions = getBranchFloorPartitions($defaultBranch->id) ?? null;
        $defaultBranch->makeHidden(['name_site', 'address_site']);

        return $defaultBranch;
    }
}
    // public function managerName($id)
    //     {

    //         $manager = Employee::find($id);

    //         if ($manager) {
    //             return $manager->first_name . ' ' . $manager->last_name;
    //         }

    //         return null;
    //     }

    // public function listBranchAndNearFilter(Request $request)
    // {
    //     $lang = $request->header('lang', 'ar');
    //     App::setLocale($lang);

    //     $validator = Validator::make($request->all(), [
    //         // latitute and longitute should either both be present, or the country, city, and area should be present
    //         'latitute' => ['nullable', 'numeric', 'regex:/^-?\d+(\.\d+)?$/', 'required_without:country_id,city_id,area_id'],
    //         'longitute' => ['nullable', 'numeric', 'regex:/^-?\d+(\.\d+)?$/', 'required_without:country_id,city_id,area_id'],
    //         'country_id' => ['nullable', 'exists:countries,id', 'required_without:latitute,longitute'],
    //         'city_id' => ['nullable', 'numeric', 'exists:cities,id', 'required_without:latitute,longitute'],
    //         'area_id' => ['nullable', 'numeric', 'exists:areas,id', 'required_without:latitute,longitute'],
    //     ], [
    //         'latitute.required_without' => __('validation.latitute.required_without'),
    //         'latitute.numeric' => __('validation.latitute.numeric'),
    //         'latitute.regex' => __('validation.latitute.regex'),
    //         'longitute.required_without' => __('validation.longitute.required_without'),
    //         'longitute.numeric' => __('validation.longitute.numeric'),
    //         'longitute.regex' => __('validation.longitute.regex'),
    //         'country_id.exists' => __('validation.country_id.exists'),
    //         'city_id.exists' => __('validation.city_id.exists'),
    //         'area_id.exists' => __('validation.area_id.exists'),
    //         'country_id.required_without' => __('validation.country_id.required_without'),
    //         'city_id.required_without' => __('validation.city_id.required_without'),
    //         'area_id.required_without' => __('validation.area_id.required_without'),
    //     ]);
    //     // $request->validate([
    //     //     // latitute and longitute should either both be present, or the country, city, and area should be present
    //     //     'latitute' => ['nullable', 'numeric', 'regex:/^-?\d+(\.\d+)?$/', 'required_without:country_id,city_id,area_id'],
    //     //     'longitute' => ['nullable', 'numeric', 'regex:/^-?\d+(\.\d+)?$/', 'required_without:country_id,city_id,area_id'],
    //     //     'country_id' => ['nullable', 'exists:countries,id', 'required_without:latitute,longitute'],
    //     //     'city_id' => ['nullable', 'exists:cities,id', 'required_without:latitute,longitute'],
    //     //     'area_id' => ['nullable', 'exists:areas,id', 'required_without:latitute,longitute'],
    //     // ], [
    //     //     'latitute.required_without' => __('validation.latitute_or_location.required'),
    //     //     'latitute.numeric' => __('validation.latitute.numeric'),
    //     //     'latitute.regex' => __('validation.latitute.regex'),
    //     //     'longitute.required_without' => __('validation.longitute_or_location.required'),
    //     //     'longitute.numeric' => __('validation.longitute.numeric'),
    //     //     'longitute.regex' => __('validation.longitute.regex'),
    //     //     'country_id.exists' => __('validation.country_id.exists'),
    //     //     'city_id.exists' => __('validation.city_id.exists'),
    //     //     'area_id.exists' => __('validation.area_id.exists'),
    //     // ]);
    //     if ($validator->fails()) {
    //         return respondError(__('validation.error'), 400, $validator->errors());
    //     }
    //     // Retrieve validated data
    //     $userLat = $request->query('latitute');
    //     $userLon = $request->query('longitute');
    //     $all = $request->query('all', 1);
    //     $searchName = $request->query('name');
    //     $countryId = $request->query('country_id');
    //     $cityId = $request->query('city_id');
    //     $areaId = $request->query('area_id');

    //     $data = [];

    //     // Determine the correct column for branch name based on language
    //     $nameColumn = ($lang === 'ar') ? 'branches.name_ar' : 'branches.name_en'; // Explicitly specify the table name
    //     $query = Branch::where('is_active', 1);

    //     // Filter by location if latitute and longitute are provided
    //     if ($userLat && $userLon) {
    //         $query->select('*')
    //             ->selectRaw("(6371 * acos(cos(radians($userLat))
    //                   * cos(radians(latitute))
    //                   * cos(radians(longitute) - radians($userLon))
    //                   + sin(radians($userLat))
    //                   * sin(radians(latitute)))) AS distance")
    //             ->whereNotNull('latitute')
    //             ->whereNotNull('longitute')
    //             ->orderBy('distance', 'asc');
    //     }
    //     // Filter by country, city, and area if provided
    //     elseif ($countryId && $cityId && $areaId) {
    //         $query->where('country_id', $countryId)
    //             ->where('city_id', $cityId)
    //             ->where('area_id', $areaId);
    //     }

    //     // Filter by branch status if requested
    //     if ($request->has('branchesStatus')) {
    //         $query->where('is_open', 1);
    //     }

    //     // Fetch branches based on the conditions
    //     $branches = $query->get();

    //     if ($branches->isEmpty()) {
    //         return response()->json(null);
    //     }

    //     $branch = $branches->first();
    //     $services = [];

    //     // Determine the services offered by the branch
    //     if ($branch->is_delivery) {
    //         $services[] = __("header.deliveryTo");
    //     }
    //     if ($branch->is_takeaway) {
    //         $services[] = __("header.pickup");
    //     }
    //     if ($branch->is_table_reservation) {
    //         $services[] = __("header.reservation");
    //     }

    //     $branch->services = implode(' - ', $services);
    //     $branch->working_times = getBranchWorkingHours($branch->id);

    //     $branch->is_default = (bool)$branch->is_default;
    //     $branch->tax_apply = (bool)$branch->tax_apply;
    //     // Convert boolean values to 0 or 1
    //     $branch->is_open = (bool)$branch->is_open;
    //     $branch->is_delivery = (bool)$branch->is_delivery;
    //     $branch->is_takeaway = (bool)$branch->is_takeaway;
    //     $branch->is_table_reservation = (bool)$branch->is_table_reservation;
    //     $branch->is_active = (bool)$branch->is_active;
    //     $branch->has_kids_area = (bool)$branch->has_kids_area;
    //     $branch->tax_application = (bool)$branch->tax_application;
    //     $branch->coupon_application = (bool)$branch->coupon_application;
    //     $branch->is_live = (bool)$branch->is_live;
    //     $branch['floor_partition'] = getBranchFloorPartitions($branch->id) ?? null;

    //     $branch->makeHidden(['name_site', 'address_site']);
    //     $data['branch'] = $branch;
    //     return ResponseWithSuccessData($lang, $data, 1);
    // }

    // public function listBranchAndNear(Request $request)
    // {
    //     $lang = $request->header('lang', 'ar');
    //     App::setLocale($lang);

    //     $request->validate([
    //         'latitute' => ['numeric', 'regex:/^-?\d+(\.\d+)?$/'],
    //         'longitute' => ['numeric', 'regex:/^-?\d+(\.\d+)?$/'],
    //         'country_id' => ['nullable', 'exists:countries,id'],
    //         'city_id' => ['nullable', 'exists:cities,id'],
    //         'area_id' => ['nullable', 'exists:areas,id'],

    //     ], [
    //         'latitute.required' => __('validation.latitute.required'),
    //         'latitute.numeric' => __('validation.latitute.numeric'),
    //         'latitute.regex' => __('validation.latitute.regex'),
    //         'longitute.required' => __('validation.longitute.required'),
    //         'longitute.numeric' => __('validation.longitute.numeric'),
    //         'longitute.regex' => __('validation.longitute.regex'),
    //         'country_id.exists' => __('validation.country_id.exists'),
    //         'city_id.exists' => __('validation.city_id.exists'),
    //         'area_id.exists' => __('validation.area_id.exists'),

    //     ]);

    //     // Retrieve validated data
    //     $userLat = $request->query('latitute');
    //     $userLon = $request->query('longitute');
    //     $all = $request->query('all', 1);
    //     $searchName = $request->query('name'); // Capture the search query for branch name
    //     $countryId = $request->query('country_id'); // Capture the country_id filter
    //     $cityId = $request->query('city_id'); // Capture the country_id filter
    //     $areaId = $request->query('area_id'); // Capture the country_id filter

    //     $currentDay = Carbon::now()->dayOfWeek;
    //     //        dd(weekDay($currentDay, 'en'));

    //     // Determine the correct column for branch name based on language
    //     $nameColumn = ($lang === 'ar') ? 'branches.name_ar' : 'branches.name_en'; // Explicitly specify the table name

    //     $branchesQuery = Branch::query()
    //         ->whereNull('branches.deleted_at')
    //         ->where('branches.is_active', 1)
    //         ->join('countries', 'branches.country_id', '=', 'countries.id')
    //         ->select(
    //             'branches.*',
    //             'countries.currency_symbol'
    //         );

    //     // Apply country_id filter if provided
    //     if ($countryId && $cityId && $areaId) {
    //         $branchesQuery->where('branches.country_id', $countryId)
    //             ->where('branches.city_id', $cityId)
    //             ->where('branches.area_id', $areaId);
    //     }

    //     // Apply name search if provided
    //     if ($searchName) {
    //         $branchesQuery->where($nameColumn, 'like', "%{$searchName}%");
    //     }

    //     $get_all_branches = $branchesQuery->get()->map(function ($branch) use ($currentDay) {
    //         // Convert is_delivery and is_default to boolean
    //         $branch->is_delivery = (bool) $branch->is_delivery;
    //         $branch->is_default = (bool) $branch->is_default;
    //         $branch->tax_apply = (bool) $branch->tax_apply;
    //         $branch->tax_application = (bool) $branch->tax_application;
    //         $branch->coupon_application = (bool) $branch->coupon_application;
    //         $branch->has_kids_area = (bool) $branch->has_kids_area;

    //         $branch->is_live = (bool)$branch->is_live;
    //         $branch->is_table_reservation = (bool)$branch->is_table_reservation;
    //         $branch->is_takeaway = (bool)$branch->is_takeaway;


    //         unset($branch->opening_hour);
    //         unset($branch->closing_hour);
    //         $branch->manager_name = $this->managerName($branch->employee_id);
    //         $branchTime = DB::table('branch_times')
    //             ->where('branch_id', $branch->id)
    //             ->where('day', $currentDay)
    //             ->where('is_active', 1)
    //             ->first();

    //         $branch->opening_hour = $branchTime->opening_hour ?? null;
    //         $branch->closing_hour = $branchTime->closing_hour ?? null;
    //         //$branch->is_open = (bool) $branch->is_open;
    //         return $branch;
    //     });

    //     $get_all_branches->makeHidden(['name_site', 'address_site']);

    //     $branches = ($all == 1) ? $get_all_branches : null;

    //     $data = [];

    //     if ($userLat && $userLon) {
    //         $nearestBranch = getNearestBranch($userLat, $userLon);

    //         if ($nearestBranch) {
    //             $nearestBranch->load('country'); // Load related country
    //             $nearestBranch->is_delivery = (bool) $nearestBranch->is_delivery;
    //             $nearestBranch->is_default = (bool) $nearestBranch->is_default;
    //             $nearestBranch->tax_apply = (bool) $nearestBranch->tax_apply;
    //             $nearestBranch->tax_application = (bool) $nearestBranch->tax_application;
    //             $nearestBranch->coupon_application = (bool) $nearestBranch->coupon_application;
    //             $nearestBranch->has_kids_area = (bool) $nearestBranch->has_kids_area;

    //             $nearestBranch->is_live = (bool)$nearestBranch->is_live;
    //             $nearestBranch->is_table_reservation = (bool)$nearestBranch->is_table_reservation;
    //             $nearestBranch->is_takeaway = (bool)$nearestBranch->is_takeaway;

    //             //                unset($nearestBranch->opening_hour);
    //             //                unset($nearestBranch->closing_hour);
    //             $branchTime = DB::table('branch_times')
    //                 ->where('branch_id', $nearestBranch->id)
    //                 ->where('day', $currentDay)
    //                 ->where('is_active', 1)
    //                 ->first();

    //             $nearestBranch->opening_hour = $branchTime->opening_hour ?? null;
    //             $nearestBranch->closing_hour = $branchTime->closing_hour ?? null;
    //             $nearestBranch->manager_name = $this->managerName($nearestBranch->employee_id);
    //             $nearestBranch->currency_symbol = $nearestBranch->country->currency_symbol ?? null;
    //             $nearestBranch->floor_partitions = getBranchFloorPartitions($nearestBranch->id) ?? null;
    //             $data['branch'] = $nearestBranch;
    //         } else {
    //             $defaultBranchId = getDefaultBranch();
    //             $data['branch'] = Branch::join('countries', 'branches.country_id', '=', 'countries.id')
    //                 ->select('branches.*', 'countries.currency_symbol')
    //                 ->find($defaultBranchId);
    //             if ($data['branch']) {
    //                 $data['branch']->is_delivery = (bool) $data['branch']->is_delivery;
    //                 $data['branch']->is_default = (bool) $data['branch']->is_default;
    //                 $data['branch']->tax_apply = (bool) $data['branch']->tax_apply;
    //                 $data['branch']->tax_application = (bool) $data['branch']->tax_application;
    //                 $data['branch']->coupon_application = (bool) $data['branch']->coupon_application;
    //                 $data['branch']->has_kids_area = (bool) $data['branch']->has_kids_area;
    //                 $data['branch']->is_live = (bool) $data['branch']->is_live;
    //                 $data['branch']->is_table_reservation = (bool) $data['branch']->is_table_reservation;
    //                 $data['branch']->is_takeaway = (bool) $data['branch']->is_takeaway;


    //                 //                    unset($data['branch']->opening_hour);
    //                 //                    unset($data['branch']->closing_hour);
    //                 $branchTime = DB::table('branch_times')
    //                     ->where('branch_id', $data['branch']->id)
    //                     ->where('day', $currentDay)
    //                     ->where('is_active', 1)
    //                     ->first();

    //                 $data['branch']->opening_hour = $branchTime->opening_hour ?? null;
    //                 $data['branch']->closing_hour = $branchTime->closing_hour ?? null;
    //                 $data['branch']->manager_name = $this->managerName($data['branch']->employee_id);
    //                 $data['branch']->floor_partitions = getBranchFloorPartitions($data['branch']->id) ?? null;
    //                 $data['branch']->makeHidden(['name_site', 'address_site']);
    //             }

    //             // $defaultBranchId = getDefaultBranch();
    //             // $data['branch'] = Branch::find($defaultBranchId);
    //             // $defaultBranchId->is_delivery = (bool) $defaultBranchId->is_delivery;
    //             // $defaultBranchId->is_default = (bool) $defaultBranchId->is_default;
    //             // unset($defaultBranchId->opening_hour);
    //             // unset($defaultBranchId->closing_hour);
    //             // $defaultBranchId->manager_name = $this->managerName($defaultBranchId->employee_id);

    //             //$defaultBranchId->is_open = (bool) $defaultBranchId->is_open;

    //         }
    //         $data['branch']->makeHidden(['name_site', 'address_site']);
    //     } else {
    //         $defaultBranchId = getDefaultBranch();
    //         $defaultBranch = Branch::join('countries', 'branches.country_id', '=', 'countries.id')
    //             ->select('branches.*', 'countries.currency_symbol')
    //             ->find($defaultBranchId);
    //         if ($defaultBranch) {
    //             $defaultBranch->is_delivery = (bool) $defaultBranch->is_delivery;
    //             $defaultBranch->is_default = (bool) $defaultBranch->is_default;
    //             $defaultBranch->tax_apply = (bool) $defaultBranch->tax_apply;
    //             $defaultBranch->tax_application = (bool) $defaultBranch->tax_application;
    //             $defaultBranch->coupon_application = (bool) $defaultBranch->coupon_application;
    //             $defaultBranch->has_kids_area = (bool) $defaultBranch->has_kids_area;

    //             $defaultBranch->is_live = (bool)$defaultBranch->is_live;
    //             $defaultBranch->is_table_reservation = (bool)$defaultBranch->is_table_reservation;
    //             $defaultBranch->is_takeaway = (bool)$defaultBranch->is_takeaway;
    //             //                unset($defaultBranch->opening_hour);
    //             //                unset($defaultBranch->closing_hour);
    //             $branchTime = DB::table('branch_times')
    //                 ->where('branch_id', $defaultBranch->id)
    //                 ->where('day', $currentDay)
    //                 ->where('is_active', 1)
    //                 ->first();

    //             $defaultBranch->opening_hour = $branchTime->opening_hour ?? null;
    //             $defaultBranch->closing_hour = $branchTime->closing_hour ?? null;
    //             $defaultBranch->manager_name = $this->managerName($defaultBranch->employee_id);

    //             $defaultBranch->floor_partitions = getBranchFloorPartitions($defaultBranch->id) ?? null;

    //             //$defaultBranch->is_open = (bool) $defaultBranch->is_open;

    //             // Hide unnecessary attributes
    //             $defaultBranch->makeHidden(['name_site', 'address_site']);
    //             $data['branch'] = $defaultBranch;
    //         }
    //     }

    //     // Include all branches if fetched
    //     $data['branches'] = $branches ?? [];

    //     return ResponseWithSuccessData($lang, $data, 1);
    // }
