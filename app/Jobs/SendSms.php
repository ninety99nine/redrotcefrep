<?php

namespace App\Jobs;

use App\Models\Store;
use App\Services\OrangeSmsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSms implements ShouldQueue
{
    use Queueable;

    public $storeId;
    public $content;
    public $recipientMobileNumber;

    /**
     * Create a new job instance.
     *
     *  @param string $content - The message content to send
     *  @param string $recipientMobileNumber - The number of the recipient to receive the message e.g 26772000001
     *  @param Store|null $store - The store sending the message
     *
     * @return void
     */
    public function __construct($content, $recipientMobileNumber, $storeId = null)
    {
        $this->storeId = $storeId;
        $this->content = $content;
        $this->recipientMobileNumber = $recipientMobileNumber;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $smsEnabled = config('app.orange_sms_enabled');

            Log::info('SendSms: '.$smsEnabled);
            Log::info('SendSms: stage 1');

            if($smsEnabled) {

                Log::info('SendSms: stage 2');
                Log::info('$this->storeId: '.$this->storeId);
                $store = $this->storeId ? Store::findOrFail($this->storeId) : null;
                Log::info('$store: '.$store);

                OrangeSmsService::sendSms($this->content, $this->recipientMobileNumber, $store);

            }

        } catch (\Throwable $th) {

            Log::info('SendSms Error: '.$th->getMessage());

        }
    }
}
