<?php

namespace App\Policies;

use App\Models\User;
use App\Models\StoreUser;

class StoreUserPolicy extends BasePolicy
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
     * Determine whether the user can view any team members.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage team members');
    }

    /**
     * Determine whether the user can view the team member.
     *
     * @param User $user
     * @param StoreUser $teamMember
     * @return bool
     */
    public function view(User $user, StoreUser $teamMember): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage team members');
    }

    /**
     * Determine whether the user can add team members.
     *
     * @param User $user
     * @return bool
     */
    public function add(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage team members');
    }

    /**
     * Determine whether the user can update the team member.
     *
     * @param User $user
     * @param StoreUser $teamMember
     * @return bool
     */
    public function update(User $user, StoreUser $teamMember): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage team members');
    }

    /**
     * Determine whether the user can remove the team member.
     *
     * @param User $user
     * @param StoreUser $teamMember
     * @return bool
     */
    public function remove(User $user, StoreUser $teamMember): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage team members');
    }

    /**
     * Determine whether the user can remove any team members.
     *
     * @param User $user
     * @return bool
     */
    public function removeAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage team members');
    }
}
