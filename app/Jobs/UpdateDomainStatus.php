<?php

namespace App\Jobs;

use Throwable;
use App\Models\Domain;
use App\Enums\DomainStatus;
use Illuminate\Bus\Queueable;
use App\Services\DomainService;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UpdateDomainStatus implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return 'update-domain-status';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            // Query domains with PENDING or PROCESSING status
            $domains = Domain::whereIn('status', [
                DomainStatus::PENDING->value,
                DomainStatus::PROCESSING->value
            ]);

            if ($domains->count() > 0) {

                // Process domains in chunks to avoid memory issues
                $domains->chunk(1000, function ($domains) {

                    $domainService = new DomainService();

                    foreach ($domains as $domain) {

                        try {

                            // Check if domain is within the 3-month verification window from creation
                            $withinVerificationWindow = $domain->created_at->addMonths(3)->greaterThanOrEqualTo(now());

                            if ($withinVerificationWindow) {
                                $domainService->verifyConnection($domain);
                            }

                        } catch (Throwable $th) {

                            Log::error('Failed to verify DNS for domain: ' . $th->getMessage(), [
                                'domain' => $domain->name,
                            ]);

                        }

                    }

                });

            }
        } catch (Throwable $th) {
            Log::error('UpdateDomainStatus Job Failed: ' . $th->getMessage());
        }
    }
}
