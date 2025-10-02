<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workflow;

class WorkflowPolicy extends BasePolicy
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
     * Determine whether the user can view any workflows.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the workflow.
     *
     * @param User $user
     * @param Workflow $workflow
     * @return bool
     */
    public function view(User $user, Workflow $workflow): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create workflows.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store');
    }

    /**
     * Determine whether the user can update the workflow.
     *
     * @param User $user
     * @param Workflow $workflow
     * @return bool
     */
    public function update(User $user, Workflow $workflow): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store');
    }

    /**
     * Determine whether the user can delete the workflow.
     *
     * @param User $user
     * @param Workflow $workflow
     * @return bool
     */
    public function delete(User $user, Workflow $workflow): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store');
    }

    /**
     * Determine whether the user can update any workflows.
     *
     * @param User $user
     * @return bool
     */
    public function updateAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store');
    }

    /**
     * Determine whether the user can delete any workflows.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage store');
    }
}
