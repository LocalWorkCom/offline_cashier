<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Http\Resources\JournalLogResource;
use App\Models\JournalLog;
use Illuminate\Database\Eloquent\Builder;

class JournalLogController extends Controller
{
    public function index()
    {
         $filters = request()->all();
        $journalsLog = JournalLog::when(
            isset($filters['order_by']),
            fn($q) => $this->applyOrdering($q, $filters)
        )
            ->when(
                isset($filters['paginate']),
                fn($q) => $q->paginate($filters['per_page'] ?? 15),
                fn($q) => $q->get()
            );

        return ResponseWithSuccessData(request()->header('lang', 'ar'), new JournalLogResource($journalsLog), 1);
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
        $JournalLog = JournalLog::find($id);

        if (!$JournalLog) {
            return RespondWithBadRequestNotAvailable();
        }
        return ResponseWithSuccessData(request()->header('lang', 'ar'), new JournalLogResource($JournalLog), 1);
    }

    public function logs($id)
    {
        $logs = JournalLog::where('journal_id', $id)->get();
        return ResponseWithSuccessData(request()->header('lang', 'ar'), new JournalLogResource($logs), 1);
    }
}
