<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Review;

class ReviewPolicy extends BasePolicy
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
     * Determine whether the user can view any reviews.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the review.
     *
     * @param User $user
     * @param Review $review
     * @return bool
     */
    public function view(User $user, Review $review): bool
    {
        return $this->isStoreUserWithPermission($user, 'view reviews');
    }

    /**
     * Determine whether the user can create reviews.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage reviews');
    }

    /**
     * Determine whether the user can update the review.
     *
     * @param User $user
     * @param Review $review
     * @return bool
     */
    public function update(User $user, Review $review): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage reviews');
    }

    /**
     * Determine whether the user can delete the review.
     *
     * @param User $user
     * @param Review $review
     * @return bool
     */
    public function delete(User $user, Review $review): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage reviews');
    }

    /**
     * Determine whether the user can update any reviews.
     *
     * @param User $user
     * @return bool
     */
    public function updateAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage reviews');
    }

    /**
     * Determine whether the user can delete any reviews.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage reviews');
    }
}
