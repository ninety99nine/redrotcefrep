<?php

namespace App\Traits;

trait PricingPlanTrait
{
    /**
     * Offers subscription.
     *
     * @return bool
     */
    public function offersSubscription(): bool
    {
        return $this->offersStoreSubscription() ||
               $this->offersAiAssistantSubscription();
    }

    /**
     * Offers store subscription.
     *
     * @return bool
     */
    public function offersStoreSubscription(): bool
    {
        return isset($this->metadata['store_subscription']);
    }

    /**
     * Offers store AI Assistant subscription.
     *
     * @return bool
     */
    public function offersAiAssistantSubscription(): bool
    {
        return isset($this->metadata['ai_assistant_subscription']);
    }

    /**
     * Offers AI Assistant top up credits.
     *
     * @return bool
     */
    public function offersAiAssistantTopUpCredits(): bool
    {
        return isset($this->metadata['ai_assistant_top_up_credits']);
    }

    /**
     * Offers SMS credits.
     *
     * @return bool
     */
    public function offersSmsCredits(): bool
    {
        return isset($this->metadata['sms_credits']);
    }

    /**
     * Offers email credits.
     *
     * @return bool
     */
    public function offersEmailCredits(): bool
    {
        return isset($this->metadata['email_credits']);
    }

    /**
     * Offers Whatsapp credits.
     *
     * @return bool
     */
    public function offersWhatsappCredits(): bool
    {
        return isset($this->metadata['whatsapp_credits']);
    }
}
