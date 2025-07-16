<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use App\Http\Resources\SubscriptionResource;
use App\Http\Resources\SubscriptionResources;
use App\Http\Requests\Subscription\ShowSubscriptionRequest;
use App\Http\Requests\Subscription\ShowSubscriptionsRequest;
use App\Http\Requests\Subscription\CreateSubscriptionRequest;
use App\Http\Requests\Subscription\UpdateSubscriptionRequest;
use App\Http\Requests\Subscription\DeleteSubscriptionRequest;
use App\Http\Requests\Subscription\DeleteSubscriptionsRequest;

class SubscriptionController extends Controller
{
    /**
     * @var SubscriptionService
     */
    protected $service;

    /**
     * SubscriptionController constructor.
     *
     * @param SubscriptionService $service
     */
    public function __construct(SubscriptionService $service)
    {
        $this->service = $service;
    }

    /**
     * Show subscriptions.
     *
     * @param ShowSubscriptionsRequest $request
     * @return SubscriptionResources|array
     */
    public function showSubscriptions(ShowSubscriptionsRequest $request): SubscriptionResources|array
    {
        return $this->service->showSubscriptions($request->validated());
    }

    /**
     * Create subscription.
     *
     * @param CreateSubscriptionRequest $request
     * @return array
     */
    public function createSubscription(CreateSubscriptionRequest $request): array
    {
        return $this->service->createSubscription($request->validated());
    }

    /**
     * Delete multiple subscriptions.
     *
     * @param DeleteSubscriptionsRequest $request
     * @return array
     */
    public function deleteSubscriptions(DeleteSubscriptionsRequest $request): array
    {
        $subscriptionIds = request()->input('subscription_ids', []);
        return $this->service->deleteSubscriptions($subscriptionIds);
    }

    /**
     * Show single subscription.
     *
     * @param ShowSubscriptionRequest $request
     * @param Subscription $subscription
     * @return SubscriptionResource
     */
    public function showSubscription(ShowSubscriptionRequest $request, Subscription $subscription): SubscriptionResource
    {
        return $this->service->showSubscription($subscription);
    }

    /**
     * Update subscription.
     *
     * @param UpdateSubscriptionRequest $request
     * @param Subscription $subscription
     * @return array
     */
    public function updateSubscription(UpdateSubscriptionRequest $request, Subscription $subscription): array
    {
        return $this->service->updateSubscription($subscription, $request->validated());
    }

    /**
     * Delete subscription.
     *
     * @param DeleteSubscriptionRequest $request
     * @param Subscription $subscription
     * @return array
     */
    public function deleteSubscription(DeleteSubscriptionRequest $request, Subscription $subscription): array
    {
        return $this->service->deleteSubscription($subscription);
    }
}
