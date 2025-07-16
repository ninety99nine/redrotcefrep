<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Promotion;

class PromotionPolicy extends BasePolicy
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
     * Determine whether the user can view any promotions.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'view promotions');
    }

    /**
     * Determine whether the user can view the promotion.
     *
     * @param User $user
     * @param Promotion $promotion
     * @return bool
     */
    public function view(User $user, Promotion $promotion): bool
    {
        return $this->isStoreUserWithPermission($user, 'view promotions');
    }

    /**
     * Determine whether the user can create promotions.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage promotions');
    }

    /**
     * Determine whether the user can update the promotion.
     *
     * @param User $user
     * @param Promotion $promotion
     * @return bool
     */
    public function update(User $user, Promotion $promotion): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage promotions');
    }

    /**
     * Determine whether the user can delete the promotion.
     *
     * @param User $user
     * @param Promotion $promotion
     * @return bool
     */
    public function delete(User $user, Promotion $promotion): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage promotions');
    }

    /**
     * Determine whether the user can delete any promotions.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage promotions');
    }
}
