<?php

namespace App\Services\ReportServices;

use App\Models\TableReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class TableReservationReportService
{
    // In TableReservationReportService.php
    public function index(Request $request)
    {
        return TableReservation::with(['client', 'branch', 'transaction', 'floorPartition'])
            ->when($request->branch_id, function ($query) use ($request) {
                return $query->where('branch_id', $request->branch_id);
            })
            ->when($request->client_id, function ($query) use ($request) {
                return $query->where('client_id', $request->client_id);
            })
            ->when($request->phone, function ($query) use ($request) {
                return $query->whereHas('client', function ($q) use ($request) {
                    $q->where('phone', 'like', '%' . $request->phone . '%');
                });
            })
            ->when($request->table_id, function ($query) use ($request) {
                return $query->where('table_id', $request->table_id);
            })
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->from_date, function ($query) use ($request) {
                return $query->whereDate('date', '>=', $request->from_date);
            })
            ->when($request->to_date, function ($query) use ($request) {
                return $query->whereDate('date', '<=', $request->to_date);
            });
    }

    public function show(Request $request, string $id)
    {
        try {
            $lang = app()->getLocale();

            $reservation = TableReservation::with(['client', 'branch', 'floorPartition', 'order', 'transaction'])
                ->find($id);

            if (!$reservation) {
                return respondError(__('Reservation not found'), 2);
            }

            $data = [
                'name' => optional($reservation->client)->name ?? '',
                'phone' => optional($reservation->client)->phone ?? '',
                'branch' => [
                    'name' => optional($reservation->branch)->name ?? '',
                ],
                'time_from' => $reservation->time_from ? \Carbon\Carbon::parse($reservation->time_from)->format('H:i') : '',
                'time_to' => $reservation->time_to ? \Carbon\Carbon::parse($reservation->time_to)->format('H:i') : '',
                'booking' => $reservation->date ? \Carbon\Carbon::parse($reservation->date)->format('Y-m-d')  : '',
                'confirmed_date' => $reservation->confirmed_date ?? '',
                'confirmed_time' => $reservation->confirmed_time ? \Carbon\Carbon::parse($reservation->confirmed_time)->format('H:i') : '',
                'branch_id' => $reservation->branch_id ?? '',
                'client_id' => $reservation->client_id ?? '',
                'table_id' => $reservation->table_id ?? '', //
                'reservation_number' => $reservation->reservation_number ?? '', //
                // 'date' => $reservation->date ?? '', //
                // 'confirmed_by' => optional($reservation->client)->name ?? '',
                'floor_partition' => [
                    'name' => optional($reservation->floorPartition)->name ?? null,
                    'image' => optional($reservation->floorPartition)->image ?? null,
                ],
                'table_reservation_transaction' => [
                    'refund' => optional($reservation->transaction)->refund ?? 0,
                    'paid' => optional($reservation->transaction)->paid ?? 0,
                    'reason' => optional($reservation->transaction)->reason ?? null,
                    'payment_gateway_status' => optional($reservation->transaction)->payment_gateway_status ?? null,
                    'payment_status' => $reservation->transaction ? __('reservations.' . optional($reservation->transaction)->payment_status) : __('reservations.payment_failed'),
                    'payment_method' => $reservation->transaction ? __('reservations.' . optional($reservation->transaction)->payment_method) : __('reservations.online'),
                ],
                'reservation_type' => $reservation->reservation_type ? __('reservations.' . $reservation->reservation_type) : '',
                'adult' => $reservation->adult ?? 0,
                'kids' => $reservation->kids ?? 0,
                'men' => $reservation->men ?? 0,
                'women' => $reservation->women ?? 0,
                'personal_type' => $reservation->personal_type ? __('reservations.' . $reservation->personal_type) : '',
                'status' => __('reservations.' . $reservation->status),
                'confirmed' => [
                    'name' => $reservation->status,
                    'value' => $reservation->confirmed,
                    'date' => $reservation->confirmed_date ?? '',
                    'time' => $reservation->confirmed_time ? \Carbon\Carbon::parse($reservation->confirmed_time)->format('H:i') : '',
                ],
                'cancellation_reason' => $reservation->cancellation_reason ?? '',
                'order_number' => optional($reservation->order)->order_number ?? '',
                'confirmed_by' => optional($reservation->client)->name ?? __('reservations.NotAvailable'),

            ];

            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            logger("Error in show method: " . $e->getMessage());
            return respondError($e->getMessage(), 2);
        }
    }
}
