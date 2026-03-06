<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Http\Resources\FacilityLogResource;
use App\Models\FacilityLog;
use Illuminate\Database\Eloquent\Builder;

class FacilityLogController extends Controller
{
    public function index()
    {
         $filters = request()->all();
        $facilitiesLog = FacilityLog::when(
            isset($filters['order_by']),
            fn($q) => $this->applyOrdering($q, $filters)
        )
            ->when(
                isset($filters['paginate']),
                fn($q) => $q->paginate($filters['per_page'] ?? 15),
                fn($q) => $q->get()
            );

        return ResponseWithSuccessData(request()->header('lang', 'ar'), new  FacilityLogResource($facilitiesLog), 1);
    }

    private function applyOrdering(Builder $query, array $filters): Builder
    {
        return $query->orderBy(
            $filters['order_by'] ?? 'id',
            $filters['order_direction'] ?? 'desc'
        );
    }

    public function show($id)
    {
        $facilityLog = FacilityLog::find($id);

        if (!$facilityLog) {
            return RespondWithBadRequestNotAvailable();
        }
        return  ResponseWithSuccessData(request()->header('lang', 'ar'), new FacilityLogResource($facilityLog), 1);
    }

    public function logs($id)
    {
        $logs = FacilityLog::where('facility_id', $id)->get();
        return ResponseWithSuccessData(request()->header('lang', 'ar'), new FacilityLogResource($logs), 1);
    }
}
