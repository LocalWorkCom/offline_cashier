<?php

namespace App\Jobs;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\TableReservation;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AfterArrivalAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $today = Carbon::now();

        // loop over the table reservations
        $reservations = TableReservation::get();

        // if alert sent already skip
        foreach ($reservations as $reservation) {
            if ($reservation->after_arrival_alert_sent) {
                continue;
            }

            $reservationTime = Carbon::parse($reservation->time_from)->setDateFrom(Carbon::today());

            $branchAlertAfter = Branch::find($reservation->branch_id)->branchSettings->alert_after_arrival_minutes;

            // get the alert time -> time_from (time) + branchSettings->alert_after_arrival_minutes
            $alertTime = $reservationTime->copy()->addMinutes($branchAlertAfter);

            if ($alertTime->lte(Carbon::now())) {
                // Get employees in the reservation's branch
               
                $callCenterEmployees = getEmployeesForNotify($reservation->branch_id, now(),  'customer_service');
                if ($callCenterEmployees) {
                    foreach ($callCenterEmployees as $employee) {
                        send_push_notification(
                            $employee->device_token,
                            "العميل وصل للطاولة " . $reservation->tables->name_ar . ' منذ ' . $branchAlertAfter . ' دقائق.',
                            "The customer arrived at table " . $reservation->tables->name_en . " about " . $branchAlertAfter . " minutes ago.",
                            "العميل وصل للطاولة المحجوزة",
                            "Customer arrived to table reserved",
                            "customer_service",
                            $employee->id,
                            $employee->id,
                            $reservation->id,
                            app()->getLocale(),
                        'table'                        );
                    }
                }

                // mark as sent
                $reservation->after_arrival_alert_sent = 1;
                $reservation->save();
            }
        }
    }
}
