<?php

namespace App\Jobs;

use App\Models\Store;
use App\Services\OrangeSmsService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

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
        Log::info('smsEnabled: handle');
        $smsEnabled = config('app.sms_enabled');

        if($smsEnabled) {
            Log::info('smsEnabled: smsEnabled');

            $store = Store::findOrFail($this->storeId);
            OrangeSmsService::sendSms($this->content, $this->recipientMobileNumber, $store);

        }
    }
}
