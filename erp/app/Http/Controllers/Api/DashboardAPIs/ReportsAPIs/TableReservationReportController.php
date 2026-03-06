<?php

namespace App\Http\Controllers\Api\DashboardAPIs\ReportsAPIs;

use Illuminate\Http\Request;
use App\Models\TableReservation;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Services\ReportServices\TableReservationReportService;
use App\Services\ReportServices\BookingRevenueReportService;

class TableReservationReportController extends Controller
{
    protected $tableReservationReportService;
    protected $bookingRevenueReportService;

    public function __construct(TableReservationReportService $tableReservationReportService, BookingRevenueReportService $bookingRevenueReportService)
    {
        $this->tableReservationReportService = $tableReservationReportService;
        $this->bookingRevenueReportService = $bookingRevenueReportService;
    }
    // In your controller
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        app()->setLocale($lang);

        try {
            $query = $this->tableReservationReportService->index($request);

            // Clone the query for totals to avoid affecting the main query
            $totalQuery = clone $query;

            // Get totals
            $totalReservations = $totalQuery->count();
            $totalPaid = $totalQuery->with('transaction')->get()->sum(function ($reservation) {
                return $reservation->transaction ? $reservation->transaction->paid : 0;
            });
            $totalRefund = $totalQuery->with('transaction')->get()->sum(function ($reservation) {
                return $reservation->transaction ? $reservation->transaction->refund : 0;
            });

            // Apply pagination first, then map the results
            $paginatedResult = paginateOrGetAll($query, $request);

            // Map the items using Collection's map() method
            if (isset($paginatedResult['data']) && $paginatedResult['data'] instanceof \Illuminate\Support\Collection) {
                $paginatedResult['data'] = $paginatedResult['data']->map(function ($reservation) use ($lang) {
                    return [
                        'id' => $reservation->id ?? '',
                        'name' => $reservation->client->name ?? '',
                        'phone' => $reservation->client->phone ?? '',
                        'branch' => [
                            'name' => optional($reservation->branch)->name ?? '',
                        ],
                        'date' => $reservation->date,
                        'time_from' => \Carbon\Carbon::parse($reservation->time_from)->format('H:i'),
                        'time_to' => $reservation->time_to,
                        'confirmed_date' => $reservation->confirmed_date,
                        'confirmed_time' => $reservation->confirmed_time,
                        'branch_id' => $reservation->branch_id,
                        'client_id' => $reservation->client_id,
                        'table_id' => $reservation->table_id,
                        'floor_partition_id' => $reservation->floor_partition_id,
                        'floor_partition_name' => $reservation->floorPartition->name ?? '',
                        'reservation_type' => __('reservations.' . strtolower($reservation->reservation_type)), //$reservation->reservation_type,
                        'adult' => $reservation->adult,
                        'kids' => $reservation->kids,
                        'personal_type' => __('reservations.' . strtolower($reservation->personal_type)),//$reservation->personal_type,
                        'status' => __('reservations.' . $reservation->status),
                        'confirmed' => [
                            'name' => __('reservations.' . strtolower($reservation->status)),//$reservation->status,
                            'value' => $reservation->confirmed,
                        ],
                        'transaction' => $reservation->transaction ? [
                            'paid' => $reservation->transaction->paid,
                            'refund' => $reservation->transaction->refund
                        ] : null
                    ];
                })->all(); // Convert back to array
            }

            // Add totals to the response
            $paginatedResult['totals'] = [
                'total_reservations' => $totalReservations,
                'total_paid' => $totalPaid,
                'total_refund' => $totalRefund,
            ];
            $responseData['data'] = [
                'reservations' => $paginatedResult['data'],
                'totals' => $paginatedResult['totals']
            ];

            $responseData['meta'] = [
                'meta' => $paginatedResult['meta'],
            ];

            return ResponseWithSuccessDataPaginated($lang, $responseData, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        $exists = TableReservation::where('id', $id)->exists();
        App::setLocale($lang);

        if (!$exists) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        $response = $this->tableReservationReportService->show($request, $id);

        // If the service returns a response object, extract the data
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $responseData = $response->getData(true);
            return ResponseWithSuccessData($lang, $responseData['data'] ?? $responseData, 1);
        }

        // If the service returns raw data, use it directly
        return ResponseWithSuccessData($lang, $response, 1);
    }
}
