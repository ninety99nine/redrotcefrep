<?php

namespace App\Jobs\AutoBilling;

use Throwable;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Models\AutoBillingSchedule;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class StartAutoBillingSchedules implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return 'start-auto-billing-schedules';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{

            /**
             *  We need to limit the auto billing schedules based on the next_attempt_date.
             *  The next_attempt_date helps us to determine the acceptable date and time
             *  to qualify auto billing. We can autobill on the following conditions:
             *
             *  1) If the next_attempt_date has been reached, that is, auto bill on the same
             *     date and time or sometime after.
             *
             *  2) If the next_attempt_date is not some time more than 48 hours from the
             *     desired date and time.
             *
             *  Remember that the first billing attempt is expected to occur as soon as the
             *  subscription ends provided that we do not have any downtime or delays while
             *  processing other jobs. The second attempt will occur 1 hour later after
             *  the first, and the third attempt will occur 1 hour later after the
             *  second attempt.
             *
             *  Each pricing plan can have 1 or more "maximum auto billing attempts" e.g 365.
             *  The following is a timeline of how the auto billing occurs based on the maximum
             *  auto billing attempts set on the pricing plan (max_auto_billing_attempts).
             *
             *  ---------------------------------------------------------------------------------
             *  Theoretical Timeline for (1) Attempt:
             *
             *  1) Subscription ends                    2024-01-01 08:00:00
             *  2) Attempt #1 (occurs immediately)      2024-01-01 08:00:00
             *  ----------------------------------------------------------------------------------------
             *  Theoretical Timeline for (2) Attempts:
             *
             *  1) Subscription ends                    2024-01-01 08:00:00
             *  2) Attempt #1 (occurs immediately)      2024-01-01 08:00:00
             *  3) Attempt #2 (1 hour later)            2024-01-01 09:00:00 (Assuming attempt #1 failed)
             *  ----------------------------------------------------------------------------------------
             *  Theoretical Timeline for (3) Attempts:
             *
             *  1) Subscription ends                    2024-01-01 08:00:00
             *  2) Attempt #1 (occurs immediately)      2024-01-01 08:00:00
             *  3) Attempt #2 (1 hour later)            2024-01-01 09:00:00 (Assuming attempt #1 failed)
             *  4) Attempt #3 (1 hour later)            2024-01-01 10:00:00 (Assuming attempt #2 failed)
             *  ---------------------------------------------------------------------------------
             *  This approach continues until the maximum auto billing attempts on the
             *  pricing plan (max_auto_billing_attempts) are exhausted.
             *  ---------------------------------------------------------------------------------
             *
             *  In theory, the first attempt date is the same as the subscription end date,
             *  the second attempt date is then 1 hour after the first attempt date,
             *  the third attempt date is then 1 hour after the second attempt date.
             *  This is how the theoretical timeline might look like:
             *
             *  Subscription end|          |          |
             *                  |          |          |
             *                  |< 1 hour >|< 1 hour >|
             *                  |          |          |
             *         Attempt 1| Attempt 2| Attempt 3|
             *
             *  In practice, we might not always be able to run any of these attempts
             *  exactly on their specified next_attempt_date due to various reasons e.g
             *  system downtime, scheduled maintenance or delays due to other jobs
             *  being processed etc. For simplicity, we will only take the first
             *  attempt delays into consideration for demonstration. This is how
             *  the practical timeline might look like:
             *
             *  Subscription end|                  |          |          |
             *                  |    < x hours >   |          |          |
             *                  | unexpected delays|< 1 hour >|< 1 hour >|
             *                  |                  |          |          |
             *                  |         Attempt 1| Attempt 2| Attempt 3|
             */
            $autoBillingSchedules = AutoBillingSchedule::active()
                        ->where('next_attempt_date', '<=', Carbon::now());

            if ($autoBillingSchedules->count() > 0) {

                $autoBillingSchedules->chunk(1000, function ($auto_billing_schedules) {

                    foreach ($auto_billing_schedules as $auto_billing_schedule) {

                        StartAutoBilling::dispatch($auto_billing_schedule->id);
                    }
                });
            }

        } catch (Throwable $th) {

            Log::error('StartAutoBillingSchedules Job Failed: '. $th->getMessage());

        }
    }
}
