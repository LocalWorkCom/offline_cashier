<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Models\pointSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class pointsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');  // Default to 'ar' if not provided
            if (!CheckToken()) {
                return RespondWithBadRequest($lang, 5);
            }

            // Fetch point systems along with branch details
            $pointSystem = pointSystem::with('branches')->get();

            // Define columns that need translation for point systems
            $translateColumns = ['type']; // Add other columns as needed

            // Define columns to remove (translated columns)
            $columnsToRemove = array_map(function ($col) {
                return [$col . '_ar', $col . '_en'];
            }, $translateColumns);
            $columnsToRemove = array_merge(...$columnsToRemove);

            // Map point systems to include translated columns and remove unnecessary columns
            $point = $pointSystem->map(function ($points) use ($lang, $translateColumns, $columnsToRemove) {
                // Convert pointSystem model to an array
                $data = $points->toArray();

                // Get translated data for point system
                $data = translateDataColumns($data, $lang, $translateColumns);

                // Remove translated columns from data
                $data = removeColumns($data, $columnsToRemove);

                // Translate branch details if branch exists
                if ($points->branch) {
                    $branchData = $points->branch->toArray();

                    // Translate branch name based on language
                    $branchData['name'] = $lang == 'ar' ? $branchData['name_ar'] : $branchData['name_en'];

                    // Optionally remove the other language column
                    unset($branchData['name_ar'], $branchData['name_en']);

                    // Add translated branch data to response
                    $data['branch'] = $branchData;
                }

                return $data;
            });

            return ResponseWithSuccessData($lang, $point, 1);

        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request) {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //point_systems--add new system
        try {
            $lang = $request->header('lang', 'ar');
            App::setLocale($lang);
            $UserType =  CheckUserType();
            if ($UserType == 'client') {
                return RespondWithBadRequestData($lang,  2);
            }

            $validator = Validator::make($request->all(), [
                "type_en" => "required",
                "type_ar" => "required",
                "value_earn" => "required",
                "value_redeem" => "required",
                "active" => "required",
                "point_redeem" => "required",

                'branch_id' => [
                    'required',
                    Rule::unique('point_systems', 'branch_id')
                ],
            ]);

            if ($validator->fails()) {
                return RespondWithBadRequestWithData($validator->errors());
            }

            $new = new pointSystem();

            $new->type_en  = $request->type_en;
            $new->type_ar  = $request->type_ar;
            $new->value_earn = $request->value_earn;
            $new->value_redeem = $request->value_redeem;
            $new->point_redeem = $request->point_redeem;

            $new->active = $request->active;
            $new->branch_id = $request->branch_id;
            $new->created_by = auth()->id();
            $new->save();

            return ResponseWithSuccessData($lang, $new, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id, $branch)
    {
        try {
            // Get language header, defaulting to 'ar' if not provided
            $lang = $request->header('lang', 'ar');

            // Check token validity
            if (!CheckToken()) {
                return RespondWithBadRequest($lang, 5);
            }

            // Find the point system by the provided ID

            if ($branch) {

                $pointSystem = pointSystem::where('branch_id', $branch);
            } else {

                $pointSystem = pointSystem::find($id);
            }

            // Check if the point system exists
            if (!$pointSystem) {
                return RespondWithBadRequestData($lang, 8); // Customize error message for not found
            }

            // Define columns that need translation
            $translateColumns = ['type']; // Add other columns as needed

            // Define columns to remove (translated columns)
            $columnsToRemove = array_map(function ($col) {
                return [$col . '_ar', $col . '_en'];
            }, $translateColumns);
            $columnsToRemove = array_merge(...$columnsToRemove);

            // Convert model to an array
            $data = $pointSystem->toArray();

            // Get translated data
            $data = translateDataColumns($data, $lang, $translateColumns);

            // Remove unnecessary columns
            $data = removeColumns($data, $columnsToRemove);

            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2); // Customize error message for exceptions
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        //point_systems -- show all point system
        try {
            // Get language header, defaulting to 'ar' if not provided
            $lang = $request->header('lang', 'ar');

            // Check token validity
            if (!CheckToken()) {
                return RespondWithBadRequest($lang, 5);
            }

            // Find the point system by the provided ID
            $pointSystem = pointSystem::find($id); // Use $id from method parameter

            // Check if the point system exists
            if (!$pointSystem) {
                return RespondWithBadRequestData($lang, 8); // Customize error message for not found
            }

            // Define columns that need translation
            $translateColumns = ['type']; // Add other columns as needed

            // Define columns to remove (translated columns)
            $columnsToRemove = array_map(function ($col) {
                return [$col . '_ar', $col . '_en'];
            }, $translateColumns);
            $columnsToRemove = array_merge(...$columnsToRemove);

            // Convert model to an array
            $data = $pointSystem->toArray();

            // Get translated data
            $data = translateDataColumns($data, $lang, $translateColumns);

            // Remove unnecessary columns
            $data = removeColumns($data, $columnsToRemove);

            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2); // Customize error message for exceptions
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        //point_systems
        try {
            $lang = $request->header('lang', 'ar');
            App::setLocale($lang);
            $UserType =  CheckUserType();
            if ($UserType == 'client') {
                return RespondWithBadRequestData($lang,  2);
            }

            $validator = Validator::make($request->all(), [
                "type_en" => "required",
                "type_ar" => "required",
                "value_earn" => "required",
                "value_redeem" => "required",
                "active" => "required",
                "branch_id" => "required",
                "point_redeem" => "required",

            ]);
            if ($validator->fails()) {
                return RespondWithBadRequestWithData($validator->errors());
            }

            $new = pointSystem::findOrFail($id);

            $new->type_en  = $request->type_en;
            $new->type_ar  = $request->type_ar;
            $new->value_earn = $request->value_earn;
            $new->value_redeem = $request->value_redeem;
            $new->point_redeem = $request->point_redeem;
            $new->active = $request->active;
            $new->branch_id = $request->branch_id;
            $new->modified_by = auth()->id();
            $new->save();

            $translateColumns = ['type']; // Add other columns as needed

            // Define columns to remove (translated columns)
            $columnsToRemove = array_map(function ($col) {
                return [$col . '_ar', $col . '_en'];
            }, $translateColumns);
            $columnsToRemove = array_merge(...$columnsToRemove);

            // Convert model to an array
            $data = $new->toArray();

            // Get translated data
            $data = translateDataColumns($data, $lang, $translateColumns);

            // Remove unnecessary columns
            $data = removeColumns($data, $columnsToRemove);

            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang,  2);
        }
    }



    public function destroy(string $id)
    {
        //
    }
}
