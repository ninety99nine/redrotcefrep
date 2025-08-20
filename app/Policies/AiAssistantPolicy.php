<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AiAssistant;

class AiAssistantPolicy extends BasePolicy
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
     * Determine whether the user can view any AI assistants.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the AI assistant.
     *
     * @param User $user
     * @param AiAssistant $aiAssistant
     * @return bool
     */
    public function view(User $user, AiAssistant $aiAssistant): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create AI assistants.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the AI assistant.
     *
     * @param User $user
     * @param AiAssistant $aiAssistant
     * @return bool
     */
    public function update(User $user, AiAssistant $aiAssistant): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the AI assistant.
     *
     * @param User $user
     * @param AiAssistant $aiAssistant
     * @return bool
     */
    public function delete(User $user, AiAssistant $aiAssistant): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete any AI assistants.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return true;
    }
}
