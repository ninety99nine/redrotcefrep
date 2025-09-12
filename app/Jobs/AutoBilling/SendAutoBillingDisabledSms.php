<?php

namespace App\Jobs\AutoBilling;

use Throwable;
use App\Jobs\SendSms;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Models\AutoBillingSchedule;
use Illuminate\Queue\SerializesModels;
use App\Services\MessageCrafterService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendAutoBillingDisabledSms implements ShouldQueue, ShouldBeUnique
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

            $autoBillingSchedule = AutoBillingSchedule::with(['user', 'store', 'pricingPlan'])->findOrFail($this->autoBillingScheduleId);

            $user = $autoBillingSchedule->user;
            $pricingPlan = $autoBillingSchedule->pricingPlan;

            if($user->mobile_number && !empty($pricingPlan->auto_billing_disabled_sms_message)) {

                $smsMessage = (new MessageCrafterService)->craftAutoBillingDisabledMessage($autoBillingSchedule);
                SendSms::dispatch($smsMessage, $user->mobile_number->formatE164());

            }

        } catch (Throwable $th) {

            Log::error('StopAutoBilling Job Failed: '. $th->getMessage());

        }
    }
}
