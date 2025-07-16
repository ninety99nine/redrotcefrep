<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Courier;
use App\Services\AuthService;

class CourierPolicy extends BasePolicy
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
     * Determine whether the user can view any couriers.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the courier.
     *
     * @param User $user
     * @param Courier $courier
     * @return bool
     */
    public function view(User $user, Courier $courier): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create couriers.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the courier.
     *
     * @param User $user
     * @param Courier $courier
     * @return bool
     */
    public function update(User $user, Courier $courier): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the courier.
     *
     * @param User $user
     * @param Courier $courier
     * @return bool
     */
    public function delete(User $user, Courier $courier): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete any couriers.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return true;
    }
}
