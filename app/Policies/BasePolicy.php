<?php

namespace App\Policies;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\DB;

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
    protected function isStoreUserWithPermission(User $user, string $ability, $storeId = null): bool
    {
        $storeId = $storeId ?? getPermissionsTeamId();
        if (!$storeId) return false;

        return DB::table('store_user')
            ->join('roles', 'store_user.role_id', '=', 'roles.id')
            ->join('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where('store_user.user_id', $user->id)
            ->where('store_user.store_id', $storeId)
            ->where('permissions.name', $ability)
            ->exists();
    }
}
