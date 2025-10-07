<?php

namespace App\Jobs;

use Throwable;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use App\Services\DomainService;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use App\Enums\TransactionPaymentStatus;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class VerifyDomainPayments implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return 'verify-domain-payments';
    }

    public function handle()
    {
        try {

            $transactions = Transaction::whereIn('payment_status', [
                TransactionPaymentStatus::PENDING_PAYMENT->value,
                TransactionPaymentStatus::FAILED_PAYMENT->value,
            ])
            ->where('created_at', '>=', now()->subDays(7)) // Limit to recent transactions
            ->where('owner_type', 'domain');

            if ($transactions->count() > 0) {

                $domainService = new DomainService();

                // Process transactions in chunks to avoid memory issues
                $transactions->chunk(1000, function ($transactions) use ($domainService) {

                    foreach ($transactions as $transaction) {
                        $domainService->verifyDomainPayment($transaction);
                    }

                });

            }

        } catch (Throwable $th) {
            Log::error('VerifyDomainPayments Job Failed: ' . $th->getMessage());
        }
    }
}
