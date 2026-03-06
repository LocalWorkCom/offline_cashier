<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Models\CashierMachine;
use App\Models\CashierSetting;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Vehicle;
use App\Services\SettingsServices\VehicleService;
use Google\Service\BeyondCorp\Resource\V;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VehicleApiController extends Controller
{
    protected $VehicleService;

    public function __construct(VehicleService $VehicleService)
    {
        $this->VehicleService = $VehicleService;
    }


    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $user = auth('employee')->user();
        $vehicles = $this->VehicleService->index();
        $response = paginateOrGetAll($vehicles, $request, null, null);

        return ResponseWithSuccessDataPaginated(
            $lang,
            $response,
            1
        );
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $user = auth('employee')->user();

        $vehicle = Vehicle::with(['type', 'employee'])->where('id', $id)->first();
        if (!$vehicle) {
            return respondError($lang === 'ar' ? 'لم يتم العثور علي المركبه.' : 'vehicle id not found', 404);
        }

        return ResponseWithSuccessData($lang, $vehicle, 1);
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
            app()->setLocale($lang);

        $validator = Validator::make($request->all(), [
            'vehicle_type' => 'required|exists:vehicle_settings,id',
            'license' => 'required|string|unique:vehicles,license',

            'employee' => [
                'nullable',
                Rule::exists('employees', 'id')->where(function ($query) {
                    $query->whereIn('flag', ['driver', 'employee']);
                }),
            ],
        ]);
        if ($validator->fails()) {
            return respondError(__('validation.error'), 400,  $validator->errors());
        }
        $vehicle = $this->VehicleService->store($request->all());
        return ResponseWithSuccessData($lang, $vehicle, 1);
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
            app()->setLocale($lang);

        try {
            $validator = Validator::make($request->all(), [
                'vehicle_type' => 'required|exists:vehicle_settings,id',
                'license' => [
                    'required',
                    'string',
                    Rule::unique('vehicles', 'license')->ignore($id), // ✅ ignore current record
                ],
                'employee' => [
                    'nullable',
                    Rule::exists('employees', 'id')->where(function ($query) {
                        $query->whereIn('flag', ['driver', 'employee']);
                    }),
                ],
            ]);

            if ($validator->fails()) {
                return respondError(__('validation.error'), 400,  $validator->errors());
            }

            $vehicle = $this->VehicleService->update($id, $request->all());
            return ResponseWithSuccessData($lang, $vehicle, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'المركبة غير موجودة' : 'Vehicle not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error updating vehicle: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function destroy($id, Request $request)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $vehicle = $this->VehicleService->delete($id);
            return ResponseWithSuccessData($lang, $vehicle, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'المركبة غير موجودة' : 'Vehicle not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error updating vehicle: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
