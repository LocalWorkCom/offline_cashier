<?php

namespace App\Console;

// use App\Models\Order;
// use App\Models\Table;
// use App\Models\Employee;
// use App\Models\PaymentFrequency;
//use App\Console\Commands\HandleUserCouponCommand;
// use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\UpdateSliderCommand;
use App\Console\Commands\GeneratePayrollSheet;
use App\Jobs\ProcessReservationAutoCancellations;
use App\Console\Commands\AfterArrivalAlertCommand;
use App\Console\Commands\CheckOfferDetailsCommand;
//use App\Jobs\UpdateOfferStatuses;
//use App\Jobs\UpdateCouponStatuses;
//use App\Jobs\UpdateDiscountsStatuses;
use App\Console\Commands\BeforeArrivalAlertCommand;
use App\Console\Commands\UpdateOfferStatusesCommand;
use App\Console\Commands\UpdateCouponStatusesCommand;
use App\Models\Employee;
use App\Models\Order;
use App\Models\PaymentFrequency;
use App\Models\Table;
use Illuminate\Console\Scheduling\Schedule;
//use Illuminate\Console\View\Components\Task;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\UpdateDiscountStatusesCommand;
//use Illuminate\Console\View\Components\Task;
use App\Console\Commands\RunReservationAutoCancellations;
use App\Jobs\CheckInventoryAlertsJob;
use App\Models\InventorySetting;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $tables = Table::where('status', 'busy')
                ->where('last_busy_at', '<=', now()->subMinutes(15))
                ->get();

            foreach ($tables as $table) {
                // Check if the table has an active order (status not completed)
                $activeOrder = Order::where('table_id', $table->id)
                    ->where('date', today())
                    ->where('status', '!=', 'completed') // Adjust 'completed' to your actual status
                    ->first();

                if ($activeOrder) {
                    // Fetch the waiter assigned to the order
                    $waiter = Employee::find($activeOrder->waiter_id)->device_token; // Assuming you have a relationship between Order and Waiter


                    if ($waiter) {
                        // Send notification to the waiter
                        send_push_notification(
                            $waiter,
                            "تذكير: الطاوله [$table->table_number] مشغول ولكن لم يتم تقديم أي طلب بعد",
                            "Reminder: Table [$table->table_number] is occupied but no order has been placed yet",
                            "تنبيه طاوله مشغوله",
                            "Occupied table alert",
                            "waiter",
                            $activeOrder->waiter_id,
                            $activeOrder->waiter_id,
                            $table->id, // or request ID
                            app()->getLocale(),
                            'table'
                        );
                    }

                    // Update last_busy_at to now to trigger the next notification in 5 minutes
                    $table->update(['last_busy_at' => now()]);
                }
            }
        })->everyMinute();
        // $schedule->command('inspire')->hourly();
        $schedule->command('coupons:deactivate-expired')->daily();
        $schedule->command('employees:disable-access')->dailyAt('08:00');
        $schedule->command('offers:update-status')->hourly();
        $schedule->command('offers:check-details')->hourly();
        $schedule->command('coupons:update-status')->hourly();
        // $schedule->command('users:update-coupons-status')->hourly();
        $schedule->command('discounts:update-status')->hourly();
        $schedule->command('slider:update')->hourly();
        //        $schedule->command('invoices:check')->everySixHours();
        // $schedule->command('chats:close-inactive')->everyFiveMinutes();
        // $schedule->command('chats:reassign-inactive')->everyMinute();
        $schedule->command('einvoice:send-to-portal')->dailyAt('02:00:00');
        //auto cancel reservations job
        $schedule->job(new ProcessReservationAutoCancellations)->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('attendance:check-missing')->dailyAt('23:59');
        $notification_frequency = InventorySetting::first()->notification_frequency;
        if ($notification_frequency == 'dialy') {
            $schedule->job(new CheckInventoryAlertsJob)->daily();
        } else if ($notification_frequency == 'monthly') {
            $schedule->job(new CheckInventoryAlertsJob)->monthly();
        }

        //       $schedule->command('reservation:after-arrival-alert-command')->everyFifteenMinutes();
        //        $schedule->command('reservation:before-arrival-alert-command')->everyFifteenMinutes();

        // Monthly
        $PaymentFrequency = PaymentFrequency::query();

        $schedule->command('payroll:generate daily')
            ->dailyAt('02:00');

        $weeklyDay = PaymentFrequency::where('name_en', 'weekly')
            ->value('payment_weekday');

        if ($weeklyDay) {
            $schedule->command('payroll:generate weekly')
                ->weeklyOn($weeklyDay, '02:00');
        }

        $monthlyDay = PaymentFrequency::where('name_en', 'monthly')
            ->value('payment_monthday');

        if ($monthlyDay) {
            $schedule->command('payroll:generate monthly')
                ->monthlyOn($monthlyDay, '02:00');
        }

        $schedule->command('holidays:generate')->yearlyOn(1, 1, '00:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    protected $commands = [
        UpdateOfferStatusesCommand::class,
        UpdateDiscountStatusesCommand::class,
        UpdateCouponStatusesCommand::class,
        CheckOfferDetailsCommand::class,
        UpdateSliderCommand::class,
        //        HandleUserCouponCommand::class,
        RunReservationAutoCancellations::class,
        BeforeArrivalAlertCommand::class,
        AfterArrivalAlertCommand::class,
        GeneratePayrollSheet::class,

    ];
}
