<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AITopic;

class AiTopicPolicy extends BasePolicy
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
     * Determine whether the user can view any AI topics.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the AI topic.
     *
     * @param User $user
     * @param AITopic $aITopic
     * @return bool
     */
    public function view(User $user, AITopic $aITopic): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create AI topics.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the AI topic.
     *
     * @param User $user
     * @param AITopic $aITopic
     * @return bool
     */
    public function update(User $user, AITopic $aITopic): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the AI topic.
     *
     * @param User $user
     * @param AITopic $aITopic
     * @return bool
     */
    public function delete(User $user, AITopic $aITopic): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete any AI topics.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can pay for the AI topic.
     *
     * @param User $user
     * @param AITopic $aITopic
     * @return bool
     */
    public function pay(User $user, AITopic $aITopic): bool
    {
        return false;
    }
}
