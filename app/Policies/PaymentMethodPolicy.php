<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PaymentMethod;

class PaymentMethodPolicy extends BasePolicy
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
     * Determine whether the user can view any payment methods.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the payment method.
     */
    public function view(User $user, PaymentMethod $paymentMethod): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create payment methods.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the payment method.
     */
    public function update(User $user, PaymentMethod $paymentMethod): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the payment method.
     */
    public function delete(User $user, PaymentMethod $paymentMethod): bool
    {
        return false;
    }
}
