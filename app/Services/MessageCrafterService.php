<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\Subscription;

class MessageCrafterService
{
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
        return 'Your store '.$store->name.' is live! Customers can order via '.$store->ussd_shortcode.'. Share on WhatsApp, Facebook & flyers!';
        //  return 'Your store '.$store->name.' is live! Customers can order via '.$store->ussd_shortcode.' or ' . $store->web_link . '. Share on WhatsApp, Facebook & flyers!';
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
}
