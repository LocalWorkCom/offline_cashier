<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->header('lang');
        app()->setLocale($lang);

        $date = $request->input('date');
        $year = $request->input('year', now()->year);
        $month = $request->input('month');

        $baseQuery = LeaveRequest::query()
            ->with(['leaveTypes', 'employees.position', 'employees.department']);

        if ($date) {
            $baseQuery->whereDate('date', $date);
        }

        $totalLeaves = (clone $baseQuery)->count();

        $leaveTypesSummary = (clone $baseQuery)
            ->select('leave_type_id', DB::raw('COUNT(*) as total'))
            ->groupBy('leave_type_id')
            ->get()
            ->map(function ($item) use ($totalLeaves) {
                return [
                    'leave_type_id'   => $item->leave_type_id,
                    'leave_type_name' => optional($item->leaveTypes)->name,
                    'total'           => $item->total,
                    'percentage'      => $totalLeaves > 0
                        ? round(($item->total / $totalLeaves) * 100, 2)
                        : 0,
                ];
            });

        $monthlyRaw = LeaveRequest::with('leaveTypes')
            ->selectRaw('MONTH(date) as month, leave_type_id, COUNT(*) as total')
            ->whereYear('date', $year)
            ->when($month, function ($q, $month) {
                $q->whereMonth('date', $month);
            })
            ->groupBy('month', 'leave_type_id')
            ->orderBy('month')
            ->get();

        $monthsToMap = $month ? [$month] : range(1, 12);

        $monthlyDistribution = collect($monthsToMap)->map(function ($month) use ($monthlyRaw) {

            $types = $monthlyRaw->where('month', $month)->map(function ($row) {
                return [
                    'leave_type_id'   => $row->leave_type_id,
                    'leave_type_name' => optional($row->leaveTypes)->name,
                    'total'           => $row->total,
                ];
            })->values();

            return [
                'month'        => $month,
                'month_total'  => $types->sum('total'),
                'leave_types'  => $types,
            ];
        });

        $paginated = paginateOrGetAll($baseQuery, $request);

        $report = $paginated['data']->map(function ($item) {
            return [
                'id'              => $item->id,
                'request_num'     => $item->request_num,
                'employee_id'     => $item->employee_id,
                'employee_name'   => optional($item->employees)->full_name,
                'position_id'     => optional($item->employees)->position_id,
                'position_name'   => optional(optional($item->employees)->position)->name,
                'department_id'   => optional($item->employees)->department_id,
                'department_name' => optional(optional($item->employees)->department)->name,
                'leave_type_id'   => $item->leave_type_id,
                'leave_type_name' => optional($item->leaveTypes)->name,
                'leave_count'     => $item->leave_count,
                'status'          => $item->status,
            ];
        });

        return ResponseWithSuccessData($lang, [
            'leave_types_summary'   => $leaveTypesSummary,
            'monthly_distribution'  => $monthlyDistribution,
            'report'                => $report,
        ], 1);
    }
}
