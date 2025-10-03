<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Domain;

class DomainPolicy extends BasePolicy
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
     * Determine whether the user can view any domains.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the domain.
     *
     * @param User $user
     * @param Domain $domain
     * @return bool
     */
    public function view(User $user, Domain $domain): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create domains.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store');
    }

    /**
     * Determine whether the user can update the domain.
     *
     * @param User $user
     * @param Domain $domain
     * @return bool
     */
    public function update(User $user, Domain $domain): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store');
    }

    /**
     * Determine whether the user can delete the domain.
     *
     * @param User $user
     * @param Domain $domain
     * @return bool
     */
    public function delete(User $user, Domain $domain): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store');
    }

    /**
     * Determine whether the user can verify any domains.
     *
     * @param User $user
     * @return bool
     */
    public function verifyAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store');
    }

    /**
     * Determine whether the user can delete any domains.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store');
    }
}
