<?php

namespace Database\Seeders;

use App\Models\PricingPlan;
use App\Enums\PricingPlanType;
use Illuminate\Database\Seeder;
use App\Enums\PricingPlanBillingType;

class PricingPlanSeeder extends Seeder
{
    public function run()
    {
        //  Foreach subscription plan
        foreach($this->getPricingPlans() as $subscriptionPlan) {

            //  Create subscription plan
            PricingPlan::create($subscriptionPlan);

        }
    }

    public function getPricingPlans() {
        $pricingPlans = [
            ...$this->getUssdPricingPlans(),
            ...$this->getWebAndMobilePricingPlans()
        ];

        foreach($pricingPlans as $key => $pricingPlan) {
            $pricingPlans[$key]['active'] = 1;
            $pricingPlans[$key]['created_at'] = now();
            $pricingPlans[$key]['updated_at'] = now();
            $pricingPlans[$key]['position'] = $key + 1;
        }

        return $pricingPlans;
    }

    public function getUssdPricingPlans(): array
    {
        return [
            //  Store Plans
            [
                'price' => 1.00,
                'currency' => 'BWP',
                'name' => 'Daily @ P1',
                'discount_percentage_rate' => 0,
                'max_auto_billing_attempts' => 100000,
                'type' => PricingPlanType::STORE_SUBSCRIPTION,
                'billing_type' => PricingPlanBillingType::RECURRING,
                'trial_days' => 3,
                'auto_billing_disabled_sms_message' => 'You have been successfully unsubscribed from {{ store.name }} {{ pricingPlan.name }}. Dial *250# to subscribe.',
                'description' => '1 day subscription for store access',
                'supports_ussd' => true,
                'metadata' => [
                    'store_subscription' => [
                        'duration' => 1,
                        'frequency' => 'day'
                    ],
                    'sms_credits' => 5
                ],
                'features' => null
            ],
            [
                'price' => 5.00,
                'currency' => 'BWP',
                'name' => 'Weekly @ P5',
                'discount_percentage_rate' => 0,
                'max_auto_billing_attempts' => 100000,
                'type' => PricingPlanType::STORE_SUBSCRIPTION,
                'billing_type' => PricingPlanBillingType::RECURRING,
                'trial_days' => 3,
                'auto_billing_disabled_sms_message' => 'You have been successfully unsubscribed from {{ store.name }} {{ pricingPlan.name }}. Dial *250# to subscribe.',
                'description' => '1 week subscription for store access',
                'supports_ussd' => true,
                'metadata' => [
                    'store_subscription' => [
                        'duration' => 7,
                        'frequency' => 'day'
                    ],
                    'sms_credits' => 15
                ],
                'features' => null
            ],

            //  AI Assistant Plans
            [
                'price' => 1.00,
                'currency' => 'BWP',
                'name' => 'Daily @ P1',
                'discount_percentage_rate' => 0,
                'max_auto_billing_attempts' => 100000,
                'type' => PricingPlanType::AI_ASSISTANT_SUBSCRIPTION,
                'billing_type' => PricingPlanBillingType::RECURRING,
                'trial_days' => 0,
                'auto_billing_disabled_sms_message' => 'You have been successfully unsubscribed from AI Assist ({{ pricingPlan.name }}). Dial *250# to subscribe.',
                'description' => '1 day subscription for AI Assistant',
                'supports_ussd' => true,
                'metadata' => [
                    'ai_assistant_subscription' => [
                        'duration' => 1,
                        'frequency' => 'day',
                        'credits' => 7500
                    ]
                ],
                'features' => null
            ],

            //  AI Assistant (Top-Up) Plans
            [
                'price' => 2.00,
                'currency' => 'BWP',
                'name' => 'Top up - P2',
                'discount_percentage_rate' => 0,
                'max_auto_billing_attempts' => 1,
                'type' => PricingPlanType::AI_ASSISTANT_TOP_UP_CREDITS,
                'billing_type' => PricingPlanBillingType::ONE_TIME,
                'trial_days' => 0,
                'description' => 'Top up credits',
                'supports_ussd' => true,
                'metadata' => [
                    'ai_assistant_top_up_credits' => 7500
                ],
                'features' => null
            ],

            //  SMS Credit Plans
            [
                'price' => 5.00,
                'currency' => 'BWP',
                'name' => '10 sms alerts - P5',
                'discount_percentage_rate' => 0,
                'max_auto_billing_attempts' => 1,
                'type' => PricingPlanType::SMS_CREDITS,
                'billing_type' => PricingPlanBillingType::ONE_TIME,
                'trial_days' => 0,
                'description' => '10 sms alerts',
                'supports_ussd' => true,
                'metadata' => [
                    'sms_credits' => 10
                ],
                'features' => null
            ],

            //  Email Credit Plans
            [
                'price' => 5.00,
                'currency' => 'BWP',
                'name' => '50 email alerts - P5',
                'discount_percentage_rate' => 0,
                'max_auto_billing_attempts' => 1,
                'type' => PricingPlanType::EMAIL_CREDITS,
                'billing_type' => PricingPlanBillingType::ONE_TIME,
                'trial_days' => 0,
                'description' => '50 email alerts',
                'supports_ussd' => true,
                'metadata' => [
                    'email_credits' => 50
                ],
                'features' => null
            ],

            //  Whatsapp Credit Plans
            [
                'price' => 5.00,
                'currency' => 'BWP',
                'name' => '5 whatsapp alerts - P5',
                'discount_percentage_rate' => 0,
                'max_auto_billing_attempts' => 1,
                'type' => PricingPlanType::WHATSAPP_CREDITS,
                'billing_type' => PricingPlanBillingType::ONE_TIME,
                'trial_days' => 0,
                'description' => '5 whatsapp alerts',
                'supports_ussd' => true,
                'metadata' => [
                    'whatsapp_credits' => 5
                ],
                'features' => null
            ],

        ];
    }

    public function getWebAndMobilePricingPlans(): array
    {
        return [

            //  Store Plans
            [
                'name' => 'Premium Plan',
                'price' => 5.00,
                'currency' => 'USD',
                'discount_percentage_rate' => 0,
                'max_auto_billing_attempts' => 1,
                'type' => PricingPlanType::STORE_SUBSCRIPTION,
                'billing_type' => PricingPlanBillingType::ONE_TIME,
                'trial_days' => 0,
                'description' => '1 month premium subscription for store access',
                'supports_web' => true,
                'supports_mobile' => true,
                'metadata' => [
                    'store_subscription' => [
                        'duration' => 1,
                        'frequency' => 'month',
                    ],
                    'sms_credits' => 60
                ],
                'features' => [
                    'Unlimited WhatsApp orders',
                    'No commissions',
                    'Manual payments'
                ]
            ],
            [
                'name' => 'Premium Plan',
                'price' => 60.00,
                'currency' => 'USD',
                'discount_percentage_rate' => 0,
                'max_auto_billing_attempts' => 1,
                'type' => PricingPlanType::STORE_SUBSCRIPTION,
                'billing_type' => PricingPlanBillingType::ONE_TIME,
                'trial_days' => 0,
                'description' => '1 year premium subscription for store access',
                'supports_web' => true,
                'supports_mobile' => true,
                'metadata' => [
                    'store_subscription' => [
                        'duration' => 1,
                        'frequency' => 'year',
                    ],
                    'sms_credits' => 720
                ],
                'features' => [
                    'Unlimited WhatsApp orders',
                    'No commissions',
                    'Manual payments'
                ]
            ],
            [
                'name' => 'Business Plan',
                'price' => 15.00,
                'currency' => 'USD',
                'discount_percentage_rate' => 0,
                'max_auto_billing_attempts' => 1,
                'type' => PricingPlanType::STORE_SUBSCRIPTION,
                'billing_type' => PricingPlanBillingType::ONE_TIME,
                'trial_days' => 0,
                'description' => '1 month business subscription for store access',
                'supports_web' => true,
                'supports_mobile' => true,
                'metadata' => [
                    'store_subscription' => [
                        'duration' => 1,
                        'frequency' => 'month'
                    ],
                    'sms_credits' => 60
                ],
                'features' => [
                    'Everything in Premium plus:',
                    'Card payments (Stripe, PayPal)',
                    'Loyalty and store credits',
                    'Payment proof upload',
                    'Workflow automation',
                    'Delivery distance',
                    'CSV export/import',
                    'Analytics',
                ]
            ],
            [
                'name' => 'Business Plan',
                'price' => 180.00,
                'currency' => 'USD',
                'discount_percentage_rate' => 0,
                'max_auto_billing_attempts' => 1,
                'type' => PricingPlanType::STORE_SUBSCRIPTION,
                'billing_type' => PricingPlanBillingType::ONE_TIME,
                'trial_days' => 0,
                'description' => '1 year business subscription for store access',
                'supports_web' => true,
                'supports_mobile' => true,
                'metadata' => [
                    'store_subscription' => [
                        'duration' => 1,
                        'frequency' => 'year'
                    ],
                    'sms_credits' => 720
                ],
                'features' => [
                    'Everything in Premium plus:',
                    'Card payments (Stripe, PayPal)',
                    'Loyalty and store credits',
                    'Payment proof upload',
                    'Workflow automation',
                    'Delivery distance',
                    'CSV export/import',
                    'Analytics',
                ]
            ],

            //  AI Assistant Plans
            [
                'name' => 'Premium Plan',
                'price' => 5.00,
                'currency' => 'USD',
                'discount_percentage_rate' => 0,
                'max_auto_billing_attempts' => 1,
                'type' => PricingPlanType::AI_ASSISTANT_SUBSCRIPTION,
                'billing_type' => PricingPlanBillingType::ONE_TIME,
                'trial_days' => 0,
                'description' => '1 month premium subscription for AI Assistant',
                'supports_web' => true,
                'supports_mobile' => true,
                'metadata' => [
                    'ai_assistant_subscription' => [
                        'duration' => 1,
                        'frequency' => 'month',
                        'credits' => 225000
                    ]
                ],
                'features' => null
            ],

            //  SMS Credit Plans
            [
                'price' => 5.00,
                'currency' => 'USD',
                'name' => '100 sms alerts',
                'discount_percentage_rate' => 0,
                'max_auto_billing_attempts' => 1,
                'type' => PricingPlanType::SMS_CREDITS,
                'billing_type' => PricingPlanBillingType::ONE_TIME,
                'trial_days' => 0,
                'description' => '100 sms alerts',
                'supports_ussd' => true,
                'metadata' => [
                    'sms_credits' => 100
                ],
                'features' => null
            ],

            //  Email Credit Plans
            [
                'price' => 5.00,
                'currency' => 'USD',
                'name' => '500 email alerts',
                'discount_percentage_rate' => 0,
                'max_auto_billing_attempts' => 1,
                'type' => PricingPlanType::EMAIL_CREDITS,
                'billing_type' => PricingPlanBillingType::ONE_TIME,
                'trial_days' => 0,
                'description' => '500 email alerts',
                'supports_ussd' => true,
                'metadata' => [
                    'email_credits' => 500
                ],
                'features' => null
            ],

            //  Whatsapp Credit Plans
            [
                'price' => 5.00,
                'currency' => 'USD',
                'name' => '50 whatsapp alerts',
                'discount_percentage_rate' => 0,
                'max_auto_billing_attempts' => 1,
                'type' => PricingPlanType::WHATSAPP_CREDITS,
                'billing_type' => PricingPlanBillingType::ONE_TIME,
                'trial_days' => 0,
                'description' => '50 whatsapp alerts',
                'supports_ussd' => true,
                'metadata' => [
                    'whatsapp_credits' => 50
                ],
                'features' => null
            ],

        ];
    }
}
