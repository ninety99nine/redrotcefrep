<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Category;

class CategoryPolicy extends BasePolicy
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
     * Determine whether the user can view any categories.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the category.
     *
     * @param User $user
     * @param Category $category
     * @return bool
     */
    public function view(User $user, Category $category): bool
    {
        return $this->isStoreUserWithPermission($user, 'view products');
    }

    /**
     * Determine whether the user can create categories.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage products');
    }

    /**
     * Determine whether the user can update the category.
     *
     * @param User $user
     * @param Category $category
     * @return bool
     */
    public function update(User $user, Category $category): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage products');
    }

    /**
     * Determine whether the user can delete the category.
     *
     * @param User $user
     * @param Category $category
     * @return bool
     */
    public function delete(User $user, Category $category): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage products');
    }

    /**
     * Determine whether the user can update any categories.
     *
     * @param User $user
     * @return bool
     */
    public function updateAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage products');
    }

    /**
     * Determine whether the user can delete any categories.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage products');
    }
}
