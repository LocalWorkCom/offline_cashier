<?php

namespace App\Jobs;

use App\Events\ReservationAutoCancelled;
use App\Models\TableReservation;
use App\Models\BranchSetting;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessReservationAutoCancellations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $reservations = TableReservation::with(['branch.branchSettings', 'tables'])
            ->where('status', 'confirm')
            ->whereDate('date', '<=', Carbon::today())
            ->get();

        foreach ($reservations as $reservation) {
            try {
                $this->processReservation($reservation);
            } catch (\Exception $e) {
                Log::error("Failed to process reservation {$reservation->id}: " . $e->getMessage());
            }
        }
    }

    protected function processReservation(TableReservation $reservation)
    {
        Log::debug('Processing reservation', [
            'reservation_id' => $reservation->id,
            'date' => $reservation->date,
            'time' => $reservation->time_from,
            'type' => $reservation->reservation_type
        ]);
        $settings = $reservation->branch->branchSettings;
        $now = Carbon::now();
        $reservationDateTime = Carbon::parse(
            Carbon::parse($reservation->date)->format('Y-m-d') . ' ' .
                Carbon::parse($reservation->time_from)->format('H:i:s')
        );
        // Calculate when the reservation should be auto-cancelled
        $autoCancelTime = $reservationDateTime->copy()
            ->addMinutes($settings->tables_cancelation_time_allowed ?? 30);

        // Check if we're past the auto-cancel time and table wasn't occupied
        if ($now->greaterThan($autoCancelTime) && $reservation->tables->status != 2) {
            $this->autoCancelReservation($reservation, $settings);
        }
    }

    protected function autoCancelReservation(TableReservation $reservation, BranchSetting $settings)
    {
        $reservation->update([
            'status' => 'cancel',
            'cancellation_reason' => 'reservation cancelled automotically for not showing up',
        ]);

        if ($reservation->reservation_type === 'with') {
            $reservation->order()->update([
                'status' => 'cancelled',
                'print_status' => 'cancelled'
            ]);

            $reservation->order->tracking()->create([
                'order_id' => $reservation->order->id,
                'order_status' => 'cancelled'
            ]);
            $this->processOrderRefund($reservation, $settings);
        } else {
            $this->processSimpleRefund($reservation, $settings);
        }

        Log::info("Reservation {$reservation->id} auto-cancelled", [
            'type' => $reservation->reservation_type,
            'payment_status' => $reservation->transaction?->payment_status
        ]);
        ReservationAutoCancelled::dispatch($reservation);
    }
    protected function processOrderRefund($reservation, $settings)
    {
        $transaction = $reservation->order->transaction;

        if ($transaction->payment_status == 'part') {
            $refundAmount = $this->calculateRefundAmount(
                $transaction->paid,
                $settings->deposit_with_order_deduction_policy,
                $settings->deposit_with_order_deduction_percentage
            );

            $transaction->update([
                'is_refund' => true,
                'refund' => $refundAmount,
            ]);
        }
        if ($transaction->payment_status == 'paid') {
            $refundAmount = $this->calculateRefundAmount(
                $transaction->paid,
                $settings->full_paid_order_deduction_policy,
                $settings->full_paid_order_deduction_percentage
            );

            $transaction->update([
                'is_refund' => true,
                'refund' => $refundAmount,
            ]);
        }
        if ($transaction->payment_status == 'unpaid') {
            $reservation->update(['client_flag' => 1]);
        }
    }
    protected function processSimpleRefund($reservation, $settings)
    {
        $transaction = $reservation->transaction;
        if ($transaction->payment_status == 'unpaid') {
            $reservation->update(['client_flag' => 1]);
        }
        if ($transaction->payment_status === 'part') {
            $refundAmount = $this->calculateRefundAmount(
                $transaction->paid,
                $settings->deposit_without_order_deduction_policy,
                $settings->deposit_without_order_deduction_percentage
            );

            $transaction->update([
                'is_refund' => true,
                'refund' => $refundAmount,
            ]);
        }
    }

    protected function calculateRefundAmount($paidAmount, $policy, $percentage)
    {
        switch ($policy) {
            case 'none':
                return $paidAmount;
            case 'full':
                return 0;
            case 'part':
                return $paidAmount - ($percentage / 100) * $paidAmount;
            default:
                return 0;
        }
    }
}
