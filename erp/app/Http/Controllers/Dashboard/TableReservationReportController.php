<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\ReportServices\TableReservationReportService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;

class TableReservationReportController extends Controller
{
    protected $tableReservationService;

    public function __construct(TableReservationReportService $tableReservationService)
    {
        $this->tableReservationService = $tableReservationService;
    }

    public function index(Request $request)
    {
        // Get the query builder from the service
        $query = $this->tableReservationService->index($request);

        // Execute the query and get the results
        $reservations = $query->get();

        // Calculate totals
        $totalReservations = $reservations->count();
        $totalPaid = $reservations->sum(function ($reservation) {
            return $reservation->transaction ? $reservation->transaction->paid : 0;
        });
        $totalRefund = $reservations->sum(function ($reservation) {
            return $reservation->transaction ? $reservation->transaction->refund : 0;
        });

        $totals = [
            'total_reservations' => $totalReservations,
            'total_paid' => $totalPaid,
            'total_refund' => $totalRefund,
        ];

        // Get additional data needed for filters
        $clients = User::select('id', 'name')->get();
        $branches = Branch::select('id', 'name_ar', 'name_en')->get();
        return view(
            'dashboard.reports.table_reservations.list',
            compact('reservations', 'totals', 'clients', 'branches')
        );
    }
    public function show(Request $request, $id)
    {
        $response = $this->tableReservationService->show($request, $id);
        $responseData = $response->original;

        $reservation =  $responseData['data'];

        return view('dashboard.reports.table_reservations.show', compact('reservation'));
    }
}
