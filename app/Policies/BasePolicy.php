<?php

namespace App\Policies;

use App\Models\User;
use App\Services\AuthService;

class BasePolicy
{
    /**
     * @var AuthService
     */
    protected $authService;

    /**
     * Create a new policy instance.
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Check if the user has the specified permission within the store.
     *
     * @param User $user
     * @param string $ability
     * @return bool
     */
    protected function isStoreUserWithPermission(User $user, string $ability): bool
    {
        $storeId = getPermissionsTeamId();  // StorePermission Middleware sets the store id as the team id
        $isStoreMember = $storeId && $user->stores()->where('stores.id', $storeId)->exists();

        return $isStoreMember && $user->hasPermissionTo($ability);
    }
}
