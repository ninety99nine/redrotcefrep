<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Jobs\SendSms;
use App\Models\Store;
use App\Enums\Platform;
use App\Enums\CacheName;
use App\Enums\Association;
use Illuminate\Support\Str;
use App\Models\PricingPlan;
use App\Models\Transaction;
use App\Models\AiAssistant;
use App\Models\PaymentMethod;
use App\Enums\PricingPlanType;
use App\Enums\PaymentMethodType;
use Illuminate\Support\Facades\DB;
use App\Services\PhoneNumberService;
use Illuminate\Support\Facades\Auth;
use App\Services\TransactionService;
use App\Enums\PricingPlanBillingType;
use App\Enums\TransactionFailureType;
use App\Services\OrangeAirtimeService;
use App\Enums\TransactionPaymentStatus;
use App\Services\DirectPayOnlineService;
use App\Enums\TransactionVerificationType;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\PricingPlanResource;
use App\Http\Resources\PricingPlanResources;

class PricingPlanService extends BaseService
{
    /**
     * Show pricing plans.
     *
     * @param array $data
     * @return PricingPlanResources|array
     */
    public function showPricingPlans(array $data): PricingPlanResources|array
    {
        $type = isset($data['type']) ? PricingPlanType::tryFrom($data['type']) : null;
        $platform = isset($data['platform']) ? Platform::tryFrom($data['platform']) : null;
        $association = isset($data['association']) ? Association::tryFrom($data['association']) : null;
        $billingType = isset($data['billing_type']) ? PricingPlanBillingType::tryFrom($data['billing_type']) : null;

        if($association == Association::SUPER_ADMIN) {
            $query = PricingPlan::query();
        }else{

            $query = PricingPlan::active();

            if($type) {
                $query = $query->where('type', $type->value);
            }

            if($billingType) {
                $query = $query->where('billing_type', $billingType->value);
            }

            if($platform == Platform::MOBILE) {
                $query = $query->supportsMobile();
            }else if($platform == Platform::USSD) {
                $query = $query->supportsUssd();
            }else{
                $query = $query->supportsWeb();
            }

        }

        $query = $query->when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create pricing plan.
     *
     * @param array $data
     * @return array
     */
    public function createPricingPlan(array $data): array
    {
        $pricingPlan = PricingPlan::create($data);
        return $this->showCreatedResource($pricingPlan);
    }

    /**
     * Delete pricing plans.
     *
     * @param array $pricingPlanIds
     * @return array
     * @throws Exception
     */
    public function deletePricingPlans(array $pricingPlanIds): array
    {
        $pricingPlans = PricingPlan::whereIn('id', $pricingPlanIds)->get();

        if ($totalPricingPlans = $pricingPlans->count()) {

            foreach ($pricingPlans as $pricingPlan) {

                $this->deletePricingPlan($pricingPlan);

            }

            return ['message' => $totalPricingPlans . ($totalPricingPlans == 1 ? ' Pricing plan' : ' Pricing plans') . ' deleted'];

        } else {
            throw new Exception('No pricing plans deleted');
        }
    }

    /**
     * Show pricing plan.
     *
     * @param PricingPlan $pricingPlan
     * @return PricingPlanResource
     */
    public function showPricingPlan(PricingPlan $pricingPlan): PricingPlanResource
    {
        return $this->showResource($pricingPlan);
    }

    /**
     * Update pricing plan.
     *
     * @param PricingPlan $pricingPlan
     * @param array $data
     * @return array
     */
    public function updatePricingPlan(PricingPlan $pricingPlan, array $data): array
    {
        $pricingPlan->update($data);
        return $this->showUpdatedResource($pricingPlan);
    }

    /**
     * Delete pricing plan.
     *
     * @param PricingPlan $pricingPlan
     * @return array
     * @throws Exception
     */
    public function deletePricingPlan(PricingPlan $pricingPlan): array
    {
        $deleted = $pricingPlan->delete();

        if ($deleted) {
            return ['message' => 'Pricing plan deleted'];
        } else {
            throw new Exception('Pricing plan delete unsuccessful');
        }
    }

    /**
     * Pay pricing plan.
     *
     * @param PricingPlan $pricingPlan
     * @param array $data
     * @return TransactionResource|array
     * @throws Exception
     */
    public function payPricingPlan(PricingPlan $pricingPlan, array $data): TransactionResource|array
    {
        $user = $store = $aiAssistant = null;

        $storeId = $data['store_id'] ?? null;
        $paymentMethodId = $data['payment_method_id'] ?? null;
        $paymentMethodType = $data['payment_method_type'] ?? null;
        $createdUsingAutoBilling = isset($data['auto_bill']) && $data['auto_bill'] == true;

        if ($createdUsingAutoBilling) {
            $user = User::find($data['user_id']);
            throw new Exception('The user does not exist');
        } else {
            $user = Auth::user();
        }

        $requiresStore = $pricingPlan->offersStoreSubscription() ||
                         $pricingPlan->offersWhatsappCredits() ||
                         $pricingPlan->offersEmailCredits() ||
                         $pricingPlan->offersSmsCredits();

        if ($requiresStore) {
            $store = Store::find($storeId);
            if (!$store) throw new Exception('The store does not exist');
        }

        $requiresAiAssistant = $pricingPlan->offersAiAssistantSubscription() ||
                               $pricingPlan->offersAiAssistantTopUpCredits();

        if($requiresAiAssistant) {
            $aiAssistant = $user->aiAssistant;
            if (!$aiAssistant) throw new Exception('The AI Assistant does not exist');
        }

        if ($paymentMethodId) {
            $paymentMethod = PaymentMethod::find($paymentMethodId);
        } else if ($paymentMethodType) {
            $paymentMethod = PaymentMethod::whereType($paymentMethodType)->first();
        }

        if (!$paymentMethod->active) {
            throw new Exception('The ' . $paymentMethod->name . ' payment method has been deactivated');
        }

        $transactionPayload = $this->prepareTransactionPayload($user, $store, $aiAssistant, $pricingPlan, $paymentMethod, $createdUsingAutoBilling);
        $transaction = Transaction::create($transactionPayload);

        $transaction->setRelation('owner', $pricingPlan);
        if($store) $transaction->setRelation('store', $store);
        if($aiAssistant) $transaction->setRelation('aiAssistant', $aiAssistant);

        if ($paymentMethod->type == PaymentMethodType::DPO->value) {

            $companyToken = config('app.dpo_company_token');
            $dpoPaymentLinkPayload = $this->prepareDpoPaymentLinkPayload($user, $transaction);
            $metadata = DirectPayOnlineService::createPaymentLink($companyToken, $dpoPaymentLinkPayload);

            $transaction->update(['metadata' => $metadata]);
            return (new TransactionService())->showResource($transaction);

        } else if ($paymentMethod->type == PaymentMethodType::ORANGE_AIRTIME->value) {

            $msisdn = $user->mobile_number->formatE164();
            $transaction = OrangeAirtimeService::billUsingAirtime($msisdn, $transaction);

            if ($transaction->payment_status == TransactionPaymentStatus::FAILED_PAYMENT->value) {
                return (new TransactionService())->showResource($transaction);
            }
        }

        return $this->offerPricingPlan($store, $aiAssistant, $pricingPlan, $transaction);
    }

    /**
     * Verify pricing plan payment.
     *
     * @param PricingPlan $pricingPlan
     * @param Transaction $transaction
     * @return TransactionResource
     * @throws Exception
     */
    public function verifyPricingPlanPayment(PricingPlan $pricingPlan, Transaction $transaction): TransactionResource
    {
        try {

            $store = $aiAssistant = null;
            $transaction = $transaction->load(['owner', 'store', 'aiAssistant', 'paymentMethod']);

            if (!$transaction->isPaid()) {

                $paymentMethod = $transaction->paymentMethod;

                if (!$paymentMethod) {
                    throw new Exception('The transaction payment method does not exist');
                }

                $requiresStore = $pricingPlan->offersStoreSubscription() ||
                                 $pricingPlan->offersWhatsappCredits() ||
                                 $pricingPlan->offersEmailCredits() ||
                                 $pricingPlan->offersSmsCredits();

                if ($requiresStore) {
                    $store = $transaction->store;
                    if (!$store) throw new Exception('The store does not exist');
                }

                $requiresAiAssistant = $pricingPlan->offersAiAssistantSubscription() ||
                                       $pricingPlan->offersAiAssistantTopUpCredits();

                if ($requiresAiAssistant) {
                    $aiAssistant = $transaction->aiAssistant;
                    if (!$aiAssistant) throw new Exception('The AI Assistant does not exist');
                }

                if ($paymentMethod->type == PaymentMethodType::DPO->value) {

                    $companyToken = config('app.dpo_company_token');
                    $transactionToken = $transaction->metadata['dpo_transaction_token'];
                    $metadata = DirectPayOnlineService::verifyPayment($companyToken, $transactionToken);

                    $this->offerPricingPlan($store, $aiAssistant, $pricingPlan, $transaction);

                    $transaction->update([
                        'failure_type' => null,
                        'failure_reason' => null,
                        'payment_status' => TransactionPaymentStatus::PAID->value,
                        'metadata' => array_merge($transaction->metadata, $metadata)
                    ]);

                } else {

                    throw new Exception('The ' . $paymentMethod->name . ' payment method cannot be used to verify transaction payment');

                }
            }

            return (new TransactionService)->showResource($transaction);

        } catch (Exception $e) {

            $transaction->update([
                'failure_reason' => $e->getMessage(),
                'payment_status' => TransactionPaymentStatus::FAILED_PAYMENT->value,
                'failure_type' => TransactionFailureType::PAYMENT_VERIFICATION_FAILED->value
            ]);

            throw $e;

        }
    }

    /**
     * Offer pricing plan.
     *
     * @param Store|null $store
     * @param AiAssistant|null $aiAssistant
     * @param PricingPlan $pricingPlan
     * @param Transaction $transaction
     * @return array
     */
    private function offerPricingPlan(Store|null $store, AiAssistant|null $aiAssistant, PricingPlan $pricingPlan, Transaction $transaction): array
    {
        if ($pricingPlan->offersSubscription()) {

            $storeSubscription = null;
            $aiAssistantSubscription = null;
            $message = 'Subscription created';

            $paymentMethod = $transaction->paymentMethod;

            $offersStoreSubscription = $pricingPlan->offersStoreSubscription();
            $offersAiAssistantSubscription = $pricingPlan->offersAiAssistantSubscription();

            if ($offersStoreSubscription) {

                $storeSubscriptionPayload = $this->prepareStoreSubscriptionPayload($store, $pricingPlan, $transaction);
                $storeSubscriptionResponse = (new SubscriptionService())->createSubscription($storeSubscriptionPayload);
                $storeSubscription = $storeSubscriptionResponse['subscription']->resource;

                $messageCrafterService = new MessageCrafterService();

                if ($paymentMethod->type == PaymentMethodType::ORANGE_AIRTIME->value) {

                    $smsMessage = $messageCrafterService->craftStoreSubscriptionPaidMessage($store, $transaction, $storeSubscription);
                    SendSms::dispatch($smsMessage, $transaction->requestedByUser->mobile_number->formatE164());

                    $smsMessage = $messageCrafterService->craftStoreMarketingMessage($store);
                    SendSms::dispatch($smsMessage, $transaction->requestedByUser->mobile_number->formatE164());

                }

            }

            if ($offersAiAssistantSubscription) {

                $aiAssistantSubscriptionPayload = $this->prepareAiAssistantSubscriptionPayload($aiAssistant, $pricingPlan, $transaction);
                $aiAssistantSubscriptionResponse = (new SubscriptionService())->createSubscription($aiAssistantSubscriptionPayload);
                $aiAssistantSubscription = $aiAssistantSubscriptionResponse['subscription']->resource;

                if ($paymentMethod->type == PaymentMethodType::ORANGE_AIRTIME->value) {
                    $smsMessage = $messageCrafterService->craftAIAssistantSubscriptionPaidMessage($transaction, $aiAssistantSubscription);
                    SendSms::dispatch($smsMessage, $transaction->requestedByUser->mobile_number->formatE164());
                }

            }

            if (($storeSubscription || $aiAssistantSubscription) && $pricingPlan->billing_type == PricingPlanBillingType::RECURRING->value) {

                $nextAttemptDate = $storeSubscription?->end_at ?? $aiAssistantSubscription?->end_at;

                $autoBillingSchedule = [
                    'active' => 1,
                    'attempts' => 0,
                    'store_id' => $store?->id,
                    'pricing_plan_id' => $pricingPlan->id,
                    'next_attempt_date' => $nextAttemptDate,
                    'payment_method_id' => $paymentMethod->id,
                    'user_id' => $transaction->requested_by_user_id
                ];

                $existingAutoBillingSchedule = DB::table('auto_billing_schedules')->where([
                    'store_id' => $store?->id,
                    'pricing_plan_id' => $pricingPlan->id,
                    'user_id' => $transaction->requested_by_user_id
                ])->first();

                if ($existingAutoBillingSchedule) {

                    $autoBillingSchedule['total_successful_attempts'] = $existingAutoBillingSchedule->total_successful_attempts + 1;

                    DB::table('auto_billing_schedules')->where([
                        'store_id' => $store?->id,
                        'pricing_plan_id' => $pricingPlan->id,
                        'user_id' => $transaction->requested_by_user_id
                    ])->update($autoBillingSchedule);

                } else {

                    $autoBillingSchedule['id'] = Str::uuid();
                    DB::table('auto_billing_schedules')->insert($autoBillingSchedule);

                }

                (new CacheService(CacheName::TOTAL_ACTIVE_AUTO_BILLING_SCHEDULES))->append($autoBillingSchedule['user_id'])->forget();
            }
        }

        if ($pricingPlan->offersSmsCredits() || $pricingPlan->offersEmailCredits() || $pricingPlan->offersWhatsappCredits()) {
            if (!isset($message)) $message = 'Credits added';
            $prepareStoreQuotaPayload = $this->prepareStoreQuotaPayload($store, $pricingPlan);
            $store->storeQuota()->update($prepareStoreQuotaPayload);
        }

        if ($pricingPlan->offersAiAssistantTopUpCredits()) {
            if (!isset($message)) $message = 'Credits added';
            $aiAssistant->update(['remaining_paid_top_up_tokens' => $aiAssistant->ai_assistant_top_up_credits + $pricingPlan->metadata['ai_assistant_top_up_credits']]);
        }

        return [
            'successful' => true,
            'message' => $message
        ];
    }

    /**
     * Prepare store subscription payload.
     *
     * @param Store $store
     * @param PricingPlan $pricingPlan
     * @param Transaction $transaction
     * @return array
     */
    private function prepareStoreSubscriptionPayload(Store $store, PricingPlan $pricingPlan, Transaction $transaction): array
    {
        $duration = $pricingPlan->metadata['store_subscription']['duration'];
        $frequency = $pricingPlan->metadata['store_subscription']['frequency'];
        $subscription = $store->subscriptions()->orderBy('end_at', 'DESC')->first();

        $startAt = $subscription ? $subscription->end_at : now();
        $endAt = $this->calculateSubscriptionEndAt($startAt, $frequency, $duration);

        return [
            'end_at' => $endAt,
            'start_at' => $startAt,
            'owner_type' => 'store',
            'owner_id' => $store->id,
            'transaction_id' => $transaction->id,
            'pricing_plan_id' => $pricingPlan->id,
            'user_id' => $transaction->requested_by_user_id
        ];
    }

    /**
     * Prepare AI Assistant subscription payload.
     *
     * @param AiAssistant $aiAssistant
     * @param PricingPlan $pricingPlan
     * @param Transaction $transaction
     * @return array
     */
    private function prepareAiAssistantSubscriptionPayload(AiAssistant $aiAssistant, PricingPlan $pricingPlan, Transaction $transaction): array
    {
        $credits = $pricingPlan->metadata['ai_assistant_subscription']['credits'];
        $duration = $pricingPlan->metadata['ai_assistant_subscription']['duration'];
        $frequency = $pricingPlan->metadata['ai_assistant_subscription']['frequency'];
        $subscription = $aiAssistant->subscriptions()->orderBy('end_at', 'DESC')->first();

        $startAt = $subscription ? $subscription->end_at : now();
        $endAt = $this->calculateSubscriptionEndAt($startAt, $frequency, $duration);

        return [
            'end_at' => $endAt,
            'credits' => $credits,
            'start_at' => $startAt,
            'owner_type' => 'ai assistant',
            'owner_id' => $aiAssistant->id,
            'transaction_id' => $transaction->id,
            'pricing_plan_id' => $pricingPlan->id,
            'user_id' => $transaction->requested_by_user_id
        ];
    }

    /**
     * Prepare transaction payload.
     *
     * @param User $user
     * @param Store|null $store
     * @param AiAssistant|null $aiAssistant
     * @param PricingPlan $pricingPlan
     * @param PaymentMethod $paymentMethod
     * @param bool $createdUsingAutoBilling
     * @return array
     */
    private function prepareTransactionPayload(User $user, Store|null $store, AiAssistant|null $aiAssistant, PricingPlan $pricingPlan, PaymentMethod $paymentMethod, bool $createdUsingAutoBilling): array
    {
        return [
            'percentage' => 100,
            'store_id' => $store?->id,
            'owner_type' => 'pricing plan',
            'owner_id' => $pricingPlan->id,
            'requested_by_user_id' => $user->id,
            'currency' => $pricingPlan->currency,
            'ai_assistant_id' => $aiAssistant?->id,
            'amount' => $pricingPlan->price->amount,
            'payment_method_id' => $paymentMethod->id,
            'description' => $pricingPlan->description,
            'created_using_auto_billing' => $createdUsingAutoBilling,
            'payment_status' => TransactionPaymentStatus::PENDING_PAYMENT->value,
            'verification_type' => TransactionVerificationType::AUTOMATIC->value
        ];
    }

    /**
     * Prepare DPO payment link payload.
     *
     * @param User $user
     * @param Transaction $transaction
     * @return array
     */
    public function prepareDpoPaymentLinkPayload(User $user, Transaction $transaction): array
    {
        $pricingPlan = $transaction->owner;
        $companyAccRef = ucwords($pricingPlan->type);
        $metadata = ['Transaction ID' => $transaction->id];
        $customerPhone = $customerCountry = $customerDialCode = null;
        $redirectUrl = config('app.url').'/dashboard/pricing-plans/verify-payment?transaction_id='.$transaction->id;

        if ($user->mobile_number) {
            $customerCountry = $customerDialCode = $user->mobile_number->getCountry();
            $customerPhone = PhoneNumberService::getNationalPhoneNumberWithoutSpaces($user->mobile_number);
        }

        if ($store = $transaction->store) {
            $metadata['Store ID'] = $store->id;
            $redirectUrl .= '&store_id='.$store->id;
        }

        if ($aiAssistant = $transaction->aiAssistant) {
            $metadata['AI Assistant ID'] = $aiAssistant->id;
            $redirectUrl .= '&ai_assistant_id='.$aiAssistant->id;
        }

        return [
            'ptl' => 24,
            'ptlType' => 'hours',
            'companyRefUnique' => 1,
            'metadata' => $metadata,
            'emailTransaction' => true,
            'customerEmail' => $user->email,
            'companyRef' => $transaction->id,
            'companyAccRef' => $companyAccRef,
            'customerPhone' => $customerPhone,
            'customerCountry' => $customerCountry,
            'customerLastName' => $user->last_name,
            'customerDialCode' => $customerDialCode,
            'customerFirstName' => $user->first_name,
            'emailTransaction' => !empty($user->email),
            'paymentCurrency' => $pricingPlan->currency,
            'paymentAmount' => $pricingPlan->price->amount,
            'backURL' => url()->previous(),
            'redirectURL' => $redirectUrl,
            'services' => [
                [
                    'serviceDescription' => $pricingPlan->name,
                    'serviceDate' => now()->format('Y/m/d H:i')
                ]
            ]
        ];
    }

    /**
     * Prepare store quota payload.
     *
     * @param Store|null $store
     * @param PricingPlan $pricingPlan
     * @return array
     */
    private function prepareStoreQuotaPayload($store, PricingPlan $pricingPlan): array
    {
        $data = [];
        $storeQuota = $store->storeQuota()->first();

        if ($pricingPlan->offersSmsCredits()) {
            $data['sms_credits'] = $storeQuota->sms_credits + $pricingPlan->metadata['sms_credits'];
        }

        if ($pricingPlan->offersEmailCredits()) {
            $data['email_credits'] = $storeQuota->email_credits + $pricingPlan->metadata['email_credits'];
        }

        if ($pricingPlan->offersWhatsappCredits()) {
            $data['whatsapp_credits'] = $storeQuota->whatsapp_credits + $pricingPlan->metadata['whatsapp_credits'];
        }

        return $data;
    }

    /**
     * Calculate subscription end at.
     *
     * @param Carbon|null $startAt
     * @param string $frequency
     * @param int $duration
     * @return Carbon|null
     */
    public function calculateSubscriptionEndAt(Carbon|null $startAt, string $frequency, int $duration): Carbon|null
    {
        $startAt = clone ($startAt ?? now());

        switch (strtolower($frequency)) {
            case 'day':
                return $startAt->addDays($duration);
                break;
            case 'week':
                return $startAt->addWeeks($duration);
                break;
            case 'month':
                return $startAt->addMonths($duration);
                break;
            case 'year':
                return $startAt->addYears($duration);
                break;
            default:
                return null;
                break;
        }
    }
}
