<?php

namespace App\Services\SettingsServices;

use App\Models\Employee;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use App\Models\VehicleSetting;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VehicleService
{
    public function index()
    {
        $query = Vehicle::with(['type', 'employee']);
        return $query;
    }
    public function store($data)
    {
        Log::info('Starting vehicle creation', ['data' => $data]);

        try {
            return DB::transaction(function () use ($data) {
                // Create the vehicle
                $vehicle = Vehicle::create([
                    'vehicle_type' => $data['vehicle_type'],
                    'license' => $data['license'],
                    'employee_id' => $data['employee'] ?? null, // ✅ safe
                    'created_by' => authActionSave()['by'],
                    'created_by_type' => authActionSave()['type'],
                ]);

                Log::info('Vehicle created', ['vehicle_id' => $vehicle->id]);

                if (!empty($data['employee'])) {
                    Employee::where('id', $data['employee'])
                        ->update(['vehicle_id' => $vehicle->id, 'flag' => 'driver']);
                }

                $vehicle->load(['type', 'employee']);
                $vehicle->refresh();

                return $vehicle;
            });
        } catch (\Exception $e) {
            Log::error('Vehicle creation failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update($id, $data)
    {
        Log::info('Starting vehicle update', ['vehicle_id' => $id, 'data' => $data]);

        try {
            return DB::transaction(function () use ($id, $data) {
                $vehicle = Vehicle::findOrFail($id);

                $vehicle->update([
                    'vehicle_type' => $data['vehicle_type'],
                    'license' => $data['license'],
                    'employee_id' => $data['employee'] ?? null,
                    'updated_by' => authActionSave()['by'],
                    'updated_by_type' => authActionSave()['type'],
                ]);

                Log::info('Vehicle updated', ['vehicle_id' => $vehicle->id]);

                if (!empty($data['employee'])) {
                    Employee::where('id', $data['employee'])
                        ->update(['vehicle_id' => $vehicle->id, 'flag' => 'driver']);
                }

                $vehicle->load(['type', 'employee']);
                $vehicle->refresh();

                return $vehicle;
            });
        } catch (\Exception $e) {
            Log::error('Vehicle update failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    public function delete($id)
    {
        Log::info('Starting vehicle deletion', ['vehicle_id' => $id]);

        try {
            return DB::transaction(function () use ($id) {
                $vehicle = Vehicle::findOrFail($id);
                $vehicle->delete();

                Log::info('Vehicle deleted', ['vehicle_id' => $vehicle->id]);
                Employee::where('vehicle_id', $id)->update(['vehicle_id' => null]);

                return $vehicle;
            });
        } catch (\Exception $e) {
            Log::error('Vehicle deletion failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
