<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Store;

class StorePolicy extends BasePolicy
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
     * Determine whether the user can view any stores.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the store.
     *
     * @param User $user
     * @param Store $store
     * @return bool
     */
    public function view(User $user, Store $store): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create stores.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the store.
     *
     * @param User $user
     * @param Store $store
     * @return bool
     */
    public function update(User $user, Store $store): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store', $store->id);
    }

    /**
     * Determine whether the user can delete the store.
     *
     * @param User $user
     * @param Store $store
     * @return bool
     */
    public function delete(User $user, Store $store): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store', $store->id);
    }

    /**
     * Determine whether the user can delete any stores.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return false;
    }
}
