<?php

namespace App\Jobs\AutoBilling;

use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Models\AutoBillingSchedule;
use App\Services\PricingPlanService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class StartAutoBilling implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     *  @var string
     */
    protected $autoBillingScheduleId;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId()
    {
        return $this->autoBillingScheduleId;
    }

    /**
     * Create a new job instance.
     *
     * @param string $autoBillingScheduleId
     *
     * @return void
     */
    public function __construct(string $autoBillingScheduleId)
    {
        $this->autoBillingScheduleId = $autoBillingScheduleId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{

            $autoBillingSchedule = AutoBillingSchedule::with(['user', 'store', 'pricingPlan', 'paymentMethod'])->findOrFail($this->autoBillingScheduleId);

            $qualifiedActive = $autoBillingSchedule->active;

            if($qualifiedActive) {

                $user = $autoBillingSchedule->user;
                $store = $autoBillingSchedule->store;
                $pricingPlan = $autoBillingSchedule->pricingPlan;
                $paymentMethod = $autoBillingSchedule->paymentMethod;

                $data = [
                    'user' => $user,
                    'store' => $store,
                    'auto_bill' => true,
                    'payment_method' => $paymentMethod
                ];

                $result = (new PricingPlanService)->payPricingPlan($pricingPlan, $data);
                $successful = $result['successful'];

                if(!$successful) {
                    $this->updateAutoBillingScheduleOnUnsuccessfulAttempt($autoBillingSchedule);
                }

            }

        } catch (Throwable $th) {

            Log::error('StartAutoBilling Job Failed (Stage 1): '. $th->getMessage());

        }
    }

    /**
     *  Update the auto billing schedule on a unsuccessful attempt
     *
     *  @return void
     */
    private function updateAutoBillingScheduleOnUnsuccessfulAttempt($autoBillingSchedule)
    {
        try{

            $attempt = $autoBillingSchedule->attempt + 1;
            $pricingPlan = $autoBillingSchedule->pricingPlan;

            /**
             *  @var $active - Whether the auto billing is active for future attempts.
             */
            $active = is_null($pricingPlan->max_auto_billing_attempts) || $attempt < $pricingPlan->max_auto_billing_attempts;

            if($active) {
                $nextAttemptDate = now()->addHour();
            }else{
                $attempt = 0;
                $nextAttemptDate = null;
            }

            $overallAttempts = $autoBillingSchedule->overall_attempts + 1;
            $overallFailedAttempts = $autoBillingSchedule->overall_failed_attempts + 1;

            //  Update the existing auto billing schedule
            $autoBillingSchedule->update([
                'active' => $active,
                'attempt' => $attempt,
                'overall_attempts' => $overallAttempts,
                'next_attempt_date' => $nextAttemptDate,
                'overall_failed_attempts' => $overallFailedAttempts
            ]);

            if(!$active) {

                //  Deactivate the auto billing schedule since the maximum billing attempts have been reached
                SendAutoBillingDisabledSms::dispatch($autoBillingSchedule->id);

            }

        } catch (Throwable $th) {

            Log::error('StartAutoBilling Job Failed (Stage 2): '. $th->getMessage());

        }
    }
}
