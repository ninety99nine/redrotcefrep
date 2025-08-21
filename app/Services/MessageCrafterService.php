<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Support\Str;
use App\Models\PricingPlan;
use App\Models\Transaction;
use App\Models\Subscription;
use App\Models\AutoBillingSchedule;
use Illuminate\Database\Eloquent\Model;

class MessageCrafterService
{
    /**
     *  Craft the store trial subscription paid messsage.
     *
     *  @param Store $store
     *  @param Transaction $transaction
     *  @param Subscription $subscription
     *  @return string
     */
    public function craftStoreTrialSubscriptionMessage(Store $store, PricingPlan $pricingPlan) {
        return $store->name.' is now open! Enjoy ' . $pricingPlan->trial_days . ' DAYS FREE to showcase products, reach customers & make sales. After that, keep growing for just '.$pricingPlan->price->amount_with_currency.'/'.$pricingPlan->metadata['store_subscription']['frequency'].'.';
    }
    /**
     *  Craft the store subscription paid messsage.
     *
     *  @param Store $store
     *  @param Transaction $transaction
     *  @param Subscription $subscription
     *  @return string
     */
    public function craftStoreSubscriptionPaidMessage(Store $store, Transaction $transaction, Subscription $subscription) {
        return $transaction->amount->amount_with_currency.' subscription successfully paid for '.$store->name.'. Valid till '.Carbon::parse($subscription->end_date)->format('d M Y H:i'). '. Enjoy ;)';
    }

    /**
     *  Craft the store marketing messsage.
     *
     *  @param Store $store
     *  @return string
     */
    public function craftStoreMarketingMessage(Store $store) {
        return $store->name.' is live! Customers can order via '.$store->ussd_shortcode.'. Share on WhatsApp, Facebook & flyers!';
        //  return $store->name.' is live! Customers can order via '.$store->ussd_shortcode.' or ' . $store->web_link . '. Share on WhatsApp, Facebook & flyers!';
    }

    /**
     *  Craft the AI Assistant subscription paid messsage.
     *
     *  @param Transaction $transaction
     *  @param Subscription $subscription
     *
     *  @return string
     */
    public function craftAIAssistantSubscriptionPaidMessage(Transaction $transaction, Subscription $subscription) {
        return $transaction->amount->amount_with_currency.' paid for AI Assistant. Valid till '.Carbon::parse($subscription->end_date)->format('d M Y H:i');
    }

    /**
     *  Craft the new order sms messsage to send to the seller
     *
     *  @param Order $order
     *  @return string
     */
    public function craftNewOrderForSellerMessage(Order $order) {

        $store = $order->store;

        $message = 'Order #'.$order->number.' ';

        $message .= $order->summary;

        if($order->customer_first_name && $order->customer_mobile_number) {

            $message .= ' from ' . $order->customer_first_name .' '. $order->customer_mobile_number->formatNational();

        }else if($order->customer_first_name || $order->customer_mobile_number) {

            if($order->customer_first_name) {
                $message .= ' from ' . $order->customer_first_name;
            }else{
                $message .= ' from ' . $order->customer_mobile_number->formatNational();
            }

        }

        if(empty($store->sms_sender_name)) {
            $message .= '. ' . $store->name;
        }

        return $message;

    }

    /**
     *  Craft the auto billing disabled message.
     *
     *  @param AutoBillingSchedule $autoBillingSchedule
     *  @return string
     */
    public function craftAutoBillingDisabledMessage(AutoBillingSchedule $autoBillingSchedule) {

        $store = $autoBillingSchedule->store;
        $pricingPlan = $autoBillingSchedule->pricingPlan;

        return $this->replacePlaceholders($pricingPlan->auto_billing_disabled_sms_message, [
            'store' => $store,
            'pricingPlan' => $pricingPlan
        ]);

    }

    /**
     * Replace placeholders in the text with values from provided models.
     *
     * @param string $text The text containing placeholders like {{ modelName.attribute }}
     * @param array<string, Model> $models An associative array of model instances (e.g., ['store' => $store, 'pricingPlan' => $pricingPlan])
     * @return string The text with placeholders replaced by model attribute values
     */
    public function replacePlaceholders(string $text, array $models): string
    {
        // Find all placeholders like {{ modelName.attribute }}
        preg_match_all('/{{(.*?)}}/', $text, $matches);
        $placeholders = $matches[0]; // Full placeholders, e.g., {{ store.name }}
        $keys = $matches[1]; // Keys, e.g., store.name, pricingPlan.name

        $replacements = [];
        foreach ($keys as $index => $key) {

            // Trim whitespace and split the key into model and attribute
            [$modelName, $attribute] = explode('.', trim($key), 2);

            // Get the model instance by name (case-insensitive)
            $modelInstance = $models[$modelName] ?? null;

            // Initialize value as empty string for fallback
            $value = '';

            if ($modelInstance instanceof Model) {

                // Handle accessors (methods) or casted attributes
                if (method_exists($modelInstance, $attribute)) {

                    // If the attribute is an accessor (e.g., a method), call it
                    $value = $modelInstance->$attribute();

                } elseif ($modelInstance->hasGetMutator($attribute)) {

                    // Handle Laravel mutators (getAttributeNameAttribute)
                    $value = $modelInstance->$attribute;

                } elseif ($modelInstance->hasCast($attribute)) {

                    // Handle casted attributes (e.g., Money, JsonToArray)
                    $value = $modelInstance->getAttributes()[$attribute] ?? '';

                } else {

                    // Direct attribute access
                    $value = $modelInstance->$attribute ?? '';

                }

            }

            $replacements[$placeholders[$index]] = $value;
        }

        // Perform the replacement
        return Str::replace(
            array_keys($replacements),
            array_values($replacements),
            $text
        );
    }
}
