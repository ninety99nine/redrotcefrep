<?php

namespace App\Policies;

use App\Models\User;
use App\Models\StorePaymentMethod;

class StorePaymentMethodPolicy extends BasePolicy
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
     * Determine whether the user can view any store payment methods.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the store payment method.
     */
    public function view(User $user, StorePaymentMethod $storePaymentMethod): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create store payment methods.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage store');
    }

    /**
     * Determine whether the user can update the store payment method.
     */
    public function update(User $user, StorePaymentMethod $storePaymentMethod): bool
    {
        return $user->hasPermissionTo('manage store');
    }

    /**
     * Determine whether the user can delete the store payment method.
     */
    public function delete(User $user, StorePaymentMethod $storePaymentMethod): bool
    {
        return $user->hasPermissionTo('manage store');
    }

    /**
     * Determine whether the user can update any categories.
     *
     * @param User $user
     * @return bool
     */
    public function updateAny(User $user): bool
    {
        return $user->hasPermissionTo('manage store');
    }

    /**
     * Determine whether the user can delete any categories.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('manage store');
    }
}
