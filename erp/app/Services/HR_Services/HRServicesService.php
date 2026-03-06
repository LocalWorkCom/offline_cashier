<?php

namespace App\Services\HR_Services;

use App\Models\HRService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class HRServicesService
{
    // Create a new HR service
    public function create(array $data)
    {
        return HRService::create($data);
    }

    // Get all HR services
    public function getAll()
    {
        return HRService::query();
    }

    // Get a single HR service by ID
    public function getById($id)
    {
        $hrService = HRService::find($id);

        if (!$hrService) {
            return CustomRespondWithBadRequest(__('validation.NotFound'));
        }

        return $hrService;
    }

    public function getByKey($key)
    {
        $hrService = HRService::where('key', $key)->first();

        if (!$hrService) {
            return CustomRespondWithBadRequest(__('validation.NotFound'));
        }

        return $hrService->id;
    }
    // Update an existing HR service
    public function update($id, array $data)
    {
        $hrService = HRService::find($id);

        if (!$hrService) {
            return CustomRespondWithBadRequest(__('validation.NotFound'));
        }

        $hrService->update($data);

        return $hrService;
    }

    // Delete an HR service
    public function delete($id)
    {
        $hrService = HRService::find($id);

        if (!$hrService) {
            return CustomRespondWithBadRequest(__('validation.NotFound'));
        }

        $hrService->delete();

        return true;
    }
}
