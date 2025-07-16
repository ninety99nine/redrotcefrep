<?php

namespace App\Policies;

use App\Models\User;
use App\Models\StoreQuota;
use App\Services\AuthService;

class StoreQuotaPolicy extends BasePolicy
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
     * Determine whether the user can view any store quotas.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'view store quotas');
    }

    /**
     * Determine whether the user can view the store quota.
     *
     * @param User $user
     * @param StoreQuota $storeQuota
     * @return bool
     */
    public function view(User $user, StoreQuota $storeQuota): bool
    {
        return $this->isStoreUserWithPermission($user, 'view store quotas');
    }

    /**
     * Determine whether the user can create store quotas.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store quotas');
    }

    /**
     * Determine whether the user can update the store quota.
     *
     * @param User $user
     * @param StoreQuota $storeQuota
     * @return bool
     */
    public function update(User $user, StoreQuota $storeQuota): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store quotas');
    }

    /**
     * Determine whether the user can delete the store quota.
     *
     * @param User $user
     * @param StoreQuota $storeQuota
     * @return bool
     */
    public function delete(User $user, StoreQuota $storeQuota): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store quotas');
    }

    /**
     * Determine whether the user can delete any store quotas.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store quotas');
    }
}
