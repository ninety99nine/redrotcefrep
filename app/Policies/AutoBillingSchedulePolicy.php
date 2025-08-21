<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AutoBillingSchedule;

class AutoBillingSchedulePolicy extends BasePolicy
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
     * Determine whether the user can view any Auto billing schedules.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the Auto billing schedule.
     *
     * @param User $user
     * @param AutoBillingSchedule $autoBillingSchedule
     * @return bool
     */
    public function view(User $user, AutoBillingSchedule $autoBillingSchedule): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create Auto billing schedules.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the Auto billing schedule.
     *
     * @param User $user
     * @param AutoBillingSchedule $autoBillingSchedule
     * @return bool
     */
    public function update(User $user, AutoBillingSchedule $autoBillingSchedule): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the Auto billing schedule.
     *
     * @param User $user
     * @param AutoBillingSchedule $autoBillingSchedule
     * @return bool
     */
    public function delete(User $user, AutoBillingSchedule $autoBillingSchedule): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete any Auto billing schedules.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return true;
    }
}
