<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DeliveryMethod;

class DeliveryMethodPolicy extends BasePolicy
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
     * Determine whether the user can view any delivery methods.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the delivery method.
     *
     * @param User $user
     * @param DeliveryMethod $deliveryMethod
     * @return bool
     */
    public function view(User $user, DeliveryMethod $deliveryMethod): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create delivery methods.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store');
    }

    /**
     * Determine whether the user can update the delivery method.
     *
     * @param User $user
     * @param DeliveryMethod $deliveryMethod
     * @return bool
     */
    public function update(User $user, DeliveryMethod $deliveryMethod): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store');
    }

    /**
     * Determine whether the user can delete the delivery method.
     *
     * @param User $user
     * @param DeliveryMethod $deliveryMethod
     * @return bool
     */
    public function delete(User $user, DeliveryMethod $deliveryMethod): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store');
    }

    /**
     * Determine whether the user can delete any delivery methods.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store');
    }
}
