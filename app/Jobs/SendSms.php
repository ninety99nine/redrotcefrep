<?php

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSms implements ShouldQueue
{
    use Queueable;

    public $store;
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
    public function __construct($content, $recipientMobileNumber, $store = null)
    {
        $this->store = $store;
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
        $smsEnabled = config('app.sms_enabled');

        if($smsEnabled) {
            SmsService::sendOrangeSms($this->content, $this->recipientMobileNumber, $this->store);
        }
    }
}
