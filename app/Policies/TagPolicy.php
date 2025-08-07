<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy extends BasePolicy
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
     * Determine whether the user can view any tags.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the tag.
     *
     * @param User $user
     * @param Tag $tag
     * @return bool
     */
    public function view(User $user, Tag $tag): bool
    {
        return $this->isStoreUserWithPermission($user, 'view products');
    }

    /**
     * Determine whether the user can create tags.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage products');
    }

    /**
     * Determine whether the user can update the tag.
     *
     * @param User $user
     * @param Tag $tag
     * @return bool
     */
    public function update(User $user, Tag $tag): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage products');
    }

    /**
     * Determine whether the user can delete the tag.
     *
     * @param User $user
     * @param Tag $tag
     * @return bool
     */
    public function delete(User $user, Tag $tag): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage products');
    }

    /**
     * Determine whether the user can update any tags.
     *
     * @param User $user
     * @return bool
     */
    public function updateAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage products');
    }

    /**
     * Determine whether the user can delete any tags.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage products');
    }
}
