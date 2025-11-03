<?php

namespace App\Services;

use Exception;
use App\Models\AiAssistant;
use App\Models\Subscription;
use App\Http\Resources\SubscriptionResource;
use App\Http\Resources\SubscriptionResources;

class SubscriptionService extends BaseService
{
    /**
     * Show subscriptions.
     *
     * @param array $data
     * @return SubscriptionResources|array
     */
    public function showSubscriptions(array $data): SubscriptionResources|array
    {
        $query = Subscription::query()->when(!request()->has('_sort'), fn($query) => $query->latest());
        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create subscription.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createSubscription(array $data): array
    {
        $subscription = Subscription::create($data);
        $owner = $subscription->owner;

        if(($aiAssistant = $owner) instanceof AiAssistant) {

            $replaceCredits = $data['replace_credits'] ?? false;

            if($replaceCredits) {
                $totalPaidCredits = $data['credits'];
            }else{
                $totalPaidCredits = $aiAssistant->remaining_paid_tokens + $data['credits'];
            }

            $aiAssistant->update([
                'total_paid_tokens' => $totalPaidCredits,
                'remaining_paid_tokens' => $totalPaidCredits
            ]);

        }

        return $this->showCreatedResource($subscription);
    }

    /**
     * Delete subscriptions.
     *
     * @param array $subscriptionIds
     * @return array
     * @throws Exception
     */
    public function deleteSubscriptions(array $subscriptionIds): array
    {
        $subscriptions = Subscription::whereIn('id', $subscriptionIds)->get();

        if ($totalSubscriptions = $subscriptions->count()) {

            foreach ($subscriptions as $subscription) {

                $this->deleteSubscription($subscription);

            }

            return ['message' => $totalSubscriptions . ($totalSubscriptions == 1 ? ' Subscription' : ' Subscriptions') . ' deleted'];

        } else {
            throw new Exception('No subscriptions deleted');
        }
    }

    /**
     * Show subscription.
     *
     * @param Subscription $subscription
     * @return SubscriptionResource
     */
    public function showSubscription(Subscription $subscription): SubscriptionResource
    {
        return $this->showResource($subscription);
    }

    /**
     * Update subscription.
     *
     * @param Subscription $subscription
     * @param array $data
     * @return array
     */
    public function updateSubscription(Subscription $subscription, array $data): array
    {
        $subscription->update($data);
        $owner = $subscription->owner;

        if(($aiAssistant = $owner) instanceof AiAssistant) {

            $replaceCredits = $data['replace_credits'] ?? false;

            if($replaceCredits) {
                $totalPaidCredits = $data['credits'];
            }else{
                $totalPaidCredits = $aiAssistant->remaining_paid_tokens + $data['credits'];
            }

            $aiAssistant->update([
                'total_paid_tokens' => $totalPaidCredits,
                'remaining_paid_tokens' => $totalPaidCredits
            ]);

        }

        return $this->showUpdatedResource($subscription);
    }

    /**
     * Delete subscription.
     *
     * @param Subscription $subscription
     * @return array
     * @throws Exception
     */
    public function deleteSubscription(Subscription $subscription): array
    {
        $deleted = $subscription->delete();

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'Subscription deleted' : 'Subscription delete unsuccessful'
        ];
    }
}
