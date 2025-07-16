<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Subscription;

class SubscriptionPolicy extends BasePolicy
{
    /**
     * Grant all permissions to super admins who have roles not tied to any store.
     *
     * @param User $user
     * @param string $ability
     * @return bool|null
     */
    public function before(User $user, string $ability): bool|null
    {
        return $this->authService->isSuperAdmin($user) ?: null;
    }

    /**
     * Determine whether the user can view any subscriptions.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the subscription.
     *
     * @param User $user
     * @param Subscription $subscription
     * @return bool
     */
    public function view(User $user, Subscription $subscription): bool
    {
        return $this->isStoreUserWithPermission($user, 'view subscriptions');
    }

    /**
     * Determine whether the user can create subscriptions.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage subscriptions');
    }

    /**
     * Determine whether the user can update the subscription.
     *
     * @param User $user
     * @param Subscription $subscription
     * @return bool
     */
    public function update(User $user, Subscription $subscription): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage subscriptions');
    }

    /**
     * Determine whether the user can delete the subscription.
     *
     * @param User $user
     * @param Subscription $subscription
     * @return bool
     */
    public function delete(User $user, Subscription $subscription): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage subscriptions');
    }

    /**
     * Determine whether the user can delete any subscriptions.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage subscriptions');
    }
}
