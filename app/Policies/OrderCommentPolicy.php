<?php

namespace App\Policies;

use App\Models\User;
use App\Models\OrderComment;

class OrderCommentPolicy extends BasePolicy
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
     * Determine whether the user can view any order comments.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'view orders');
    }

    /**
     * Determine whether the user can view the order.
     *
     * @param User $user
     * @param OrderComment $orderComment
     * @return bool
     */
    public function view(User $user, OrderComment $orderComment): bool
    {
        return $this->isStoreUserWithPermission($user, 'view orders');
    }

    /**
     * Determine whether the user can create order comments.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage orders');
    }

    /**
     * Determine whether the user can update the order.
     *
     * @param User $user
     * @param OrderComment $orderComment
     * @return bool
     */
    public function update(User $user, OrderComment $orderComment): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage orders');
    }

    /**
     * Determine whether the user can delete the order.
     *
     * @param User $user
     * @param OrderComment $orderComment
     * @return bool
     */
    public function delete(User $user, OrderComment $orderComment): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage orders');
    }

    /**
     * Determine whether the user can update any order comments.
     *
     * @param User $user
     * @return bool
     */
    public function updateAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage orders');
    }

    /**
     * Determine whether the user can delete any order comments.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage orders');
    }

    /**
     * Determine whether the user can download any order comments.
     *
     * @param User $user
     * @return bool
     */
    public function downloadAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage orders');
    }
}
