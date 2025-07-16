<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AiAssistantTokenUsage;
use App\Services\AuthService;

class AiAssistantTokenUsagePolicy extends BasePolicy
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
     * Determine whether the user can view any AI assistant token usages.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'view ai assistant token usages');
    }

    /**
     * Determine whether the user can view the AI assistant token usage.
     *
     * @param User $user
     * @param AiAssistantTokenUsage $aiAssistantTokenUsage
     * @return bool
     */
    public function view(User $user, AiAssistantTokenUsage $aiAssistantTokenUsage): bool
    {
        return $this->isStoreUserWithPermission($user, 'view ai assistant token usages');
    }

    /**
     * Determine whether the user can create AI assistant token usages.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage ai assistant token usages');
    }

    /**
     * Determine whether the user can update the AI assistant token usage.
     *
     * @param User $user
     * @param AiAssistantTokenUsage $aiAssistantTokenUsage
     * @return bool
     */
    public function update(User $user, AiAssistantTokenUsage $aiAssistantTokenUsage): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage ai assistant token usages');
    }

    /**
     * Determine whether the user can delete the AI assistant token usage.
     *
     * @param User $user
     * @param AiAssistantTokenUsage $aiAssistantTokenUsage
     * @return bool
     */
    public function delete(User $user, AiAssistantTokenUsage $aiAssistantTokenUsage): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage ai assistant token usages');
    }

    /**
     * Determine whether the user can delete any AI assistant token usages.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $this->isStoreUserWithPermission($user, 'manage ai assistant token usages');
    }
}
