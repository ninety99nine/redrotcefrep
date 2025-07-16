<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Transaction;

class TransactionPolicy extends BasePolicy
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
     * Determine whether the user can view any transactions.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the transaction.
     *
     * @param User $user
     * @param Transaction $transaction
     * @return bool
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create transactions.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the transaction.
     *
     * @param User $user
     * @param Transaction $transaction
     * @return bool
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the transaction.
     *
     * @param User $user
     * @param Transaction $transaction
     * @return bool
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete any transactions.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can renew the transaction.
     *
     * @param User $user
     * @param Transaction $transaction
     * @return bool
     */
    public function renew(User $user, Transaction $transaction): bool
    {
        return true;
    }
}
