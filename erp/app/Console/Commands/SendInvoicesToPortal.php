<?php

namespace App\Console\Commands;

use App\Models\CashierMachine;
use App\Models\Einvoice;
use Carbon\Carbon;
use App\Models\Invoice;
use Illuminate\Console\Command;

class SendInvoicesToPortal extends Command
{
    protected $signature = 'einvoice:send-to-portal';
    protected $description = 'Send filtered e-invoices to the portal';
    public function handle()
    {
        $cashierMachineIds =  CashierMachine::pluck('id')->toArray();
        foreach ($cashierMachineIds as $cashierMachineId) {
            $cashierMachine = CashierMachine::find($cashierMachineId);
            // Get all orders with their transactions
            $einvoicesQuery = Einvoice::with(['invoice.orders.cashierMachine', 'invoice.orders.orderTransactions']) // make sure transaction is eager loaded
                ->whereNull('submissionId');
            // Default to last 24 hours if no dates provided
            $startDate = Carbon::now()->subDay();
            $endDate = Carbon::now();
            // Filter by related order's created_at and optional cashier/payment method
            $einvoicesQuery->whereHas('invoice.orders', function ($query) use ($startDate, $endDate, $cashierMachineId) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'completed');
                if ($cashierMachineId) {
                    $query->where('cashier_machine_id', $cashierMachineId);
                }
            });
            $orders = $einvoicesQuery->get();
            // if ($orders->isEmpty()) {
            //     continue;
            // }
            $cashierSettings = $cashierMachine->cashierSettings()->first();
            if (!$cashierSettings) {
                continue;
            }
            $settings = [
                'min_no_of_invoices' => $cashierSettings->min_count,
                'max_no_of_invoices' => $cashierSettings->max_count,
                'min_amount_of_money' => $cashierSettings->min_balance,
                'max_amount_of_money' => $cashierSettings->max_balance,
            ];
            // Prepare invoice data with electronic payment flag

            $invoiceData = $orders->map(function ($einvoice) {
                $order = $einvoice->invoice->orders ?? null;
                $hasElectronicPayment = false;
                if ($order && $order->orderTransactions) {
                    $hasElectronicPayment = $order->orderTransactions->contains(function ($transaction) {
                        return in_array($transaction->payment_method, ['credit', 'online', 'credit_with_delivery']);
                    });
                }
                return [
                    'inv_number' => $einvoice->invoice->invoice_num ?? null,
                    'amount' => $einvoice->invoice->total_after_tax ?? 0,
                    'is_electronic' => $hasElectronicPayment,
                ];
            })->toArray();
            $invoices = getFilteredInvoices($invoiceData, $settings); // This must return a collection or array
            if (empty($invoices) || count($invoices) === 0) {
            }
            try {
                $invoices =Einvoice::whereIn('invoice_id',Invoice::whereIn('invoice_num', $invoices)->pluck('id')->toArray())->pluck('id')->toArray() ;
                SendPortal($invoices); // Ensure this handles exceptions inside or wrap it here
            } catch (\Exception $e) {
                $this->error("Failed to send invoice ID " . $e->getMessage());
            }
        }
        $this->info('Process completed.');
    }
}
