<?php

namespace App\Services;

use Exception;
use App\Models\Role;
use App\Models\Store;
use App\Models\Permission;
use App\Http\Resources\RoleResource;
use App\Http\Resources\RoleResources;
use Illuminate\Database\Eloquent\Builder;

class RoleService extends BaseService
{
    /**
     * Show roles.
     *
     * @param array $data
     * @return RoleResources|array
     */
    public function showRoles(array $data): RoleResources|array
    {
        $storeId = $data['store_id'] ?? null;
        $search = $data['search'] ?? null;

        $query = Role::query();

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        if ($search) {
            $query->search($search);
        }

        $query = $query->when(!request()->has('_sort'), fn($query) => $query->latest());

        return $this->setQuery($query)->getOutput();
    }

    /**
     * Create role.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createRole(array $data): array
    {
        $storeId = $data['store_id'];
        $permissions = $data['permissions'] ?? [];

        Store::findOrFail($storeId);

        $role = Role::create([
            'name' => $data['name'],
            'store_id' => $storeId,
            'guard_name' => 'sanctum'
        ]);

        if (!empty($permissions)) {
            $this->syncRolePermissions($role, $permissions);
        }

        return $this->showCreatedResource($role);
    }

    /**
     * Update multiple roles.
     *
     * @param array $data
     * @return array
     */
    public function updateRoles(array $data): array
    {
        $storeId = $data['store_id'];
        $rolesData = $data['roles'] ?? [];

        $totalRoles = 0;

        foreach ($rolesData as $roleData) {
            $role = Role::where('id', $roleData['id'])
                ->where('store_id', $storeId)
                ->first();

            if (!$role) {
                continue;
            }

            $fillableData = array_intersect_key(
                $roleData,
                array_flip($role->getFillable())
            );

            $role->update($fillableData);

            $permissions = $roleData['permissions'] ?? null;
            if (!is_null($permissions)) {
                $this->syncRolePermissions($role, $permissions);
            }

            $totalRoles++;
        }

        return ['updated' => true, 'message' => $totalRoles . ($totalRoles == 1 ? ' role' : ' roles') . ' updated'];
    }

    /**
     * Delete multiple roles.
     *
     * @param array $roleIds
     * @return array
     * @throws Exception
     */
    public function deleteRoles(array $roleIds): array
    {
        $roles = Role::whereIn('id', $roleIds)->get();

        if ($totalRoles = $roles->count()) {
            foreach ($roles as $role) {
                $this->deleteRole($role);
            }

            return ['message' => $totalRoles . ($totalRoles == 1 ? ' Role' : ' Roles') . ' deleted'];
        } else {
            throw new Exception('No Roles deleted');
        }
    }

    /**
     * Show role.
     *
     * @param Role $role
     * @return RoleResource
     */
    public function showRole(Role $role): RoleResource
    {
        return $this->showResource($role);
    }

    /**
     * Update role.
     *
     * @param Role $role
     * @param array $data
     * @return array
     */
    public function updateRole(Role $role, array $data): array
    {
        $permissions = $data['permissions'] ?? null;

        $role->update($data);

        if (!is_null($permissions)) {
            $this->syncRolePermissions($role, $permissions);
        }

        return $this->showUpdatedResource($role);
    }

    /**
     * Delete role.
     *
     * @param Role $role
     * @return array
     * @throws Exception
     */
    public function deleteRole(Role $role): array
    {
        $deleted = $role->delete();

        return [
            'deleted' => $deleted,
            'message' => $deleted ? 'Role deleted' : 'Role delete unsuccessful'
        ];
    }

    /**
     * Sync permissions to role.
     *
     * @param Role $role
     * @param array $permissionIds
     * @return void
     */
    private function syncRolePermissions(Role $role, array $permissionIds): void
    {
        $permissions = Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();
        $role->syncPermissions($permissions);
    }
}
