<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Address;
use App\Services\AuthService;

class AddressPolicy extends BasePolicy
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
     * Determine whether the user can view any addresses.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'view addresses');
    }

    /**
     * Determine whether the user can view the address.
     *
     * @param User $user
     * @param Address $address
     * @return bool
     */
    public function view(User $user, Address $address): bool
    {
        return $this->isStoreUserWithPermission($user, 'view addresses');
    }

    /**
     * Determine whether the user can create addresses.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage addresses');
    }

    /**
     * Determine whether the user can update the address.
     *
     * @param User $user
     * @param Address $address
     * @return bool
     */
    public function update(User $user, Address $address): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage addresses');
    }

    /**
     * Determine whether the user can delete the address.
     *
     * @param User $user
     * @param Address $address
     * @return bool
     */
    public function delete(User $user, Address $address): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage addresses');
    }

    /**
     * Determine whether the user can delete any addresses.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage addresses');
    }
}
