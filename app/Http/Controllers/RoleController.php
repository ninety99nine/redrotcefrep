<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\RoleService;
use App\Http\Resources\RoleResource;
use App\Http\Resources\RoleResources;
use App\Http\Requests\Role\ShowRoleRequest;
use App\Http\Requests\Role\ShowRolesRequest;
use App\Http\Requests\Role\CreateRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Requests\Role\DeleteRoleRequest;
use App\Http\Requests\Role\DeleteRolesRequest;
use App\Http\Requests\Role\UpdateRolesRequest;

class RoleController extends Controller
{
    /**
     * @var RoleService
     */
    protected $service;

    /**
     * RoleController constructor.
     *
     * @param RoleService $service
     */
    public function __construct(RoleService $service)
    {
        $this->service = $service;
    }

    /**
     * Show roles.
     *
     * @param ShowRolesRequest $request
     * @return RoleResources|array
     */
    public function showRoles(ShowRolesRequest $request): RoleResources|array
    {
        return $this->service->showRoles($request->validated());
    }

    /**
     * Create role.
     *
     * @param CreateRoleRequest $request
     * @return array
     */
    public function createRole(CreateRoleRequest $request): array
    {
        return $this->service->createRole($request->validated());
    }

    /**
     * Update multiple roles.
     *
     * @param UpdateRolesRequest $request
     * @return array
     */
    public function updateRoles(UpdateRolesRequest $request): array
    {
        return $this->service->updateRoles($request->validated());
    }

    /**
     * Delete multiple roles.
     *
     * @param DeleteRolesRequest $request
     * @return array
     */
    public function deleteRoles(DeleteRolesRequest $request): array
    {
        $roleIds = request()->input('role_ids', []);
        return $this->service->deleteRoles($roleIds);
    }

    /**
     * Show role.
     *
     * @param ShowRoleRequest $request
     * @param Role $role
     * @return RoleResource
     */
    public function showRole(ShowRoleRequest $request, Role $role): RoleResource
    {
        return $this->service->showRole($role);
    }

    /**
     * Update role.
     *
     * @param UpdateRoleRequest $request
     * @param Role $role
     * @return array
     */
    public function updateRole(UpdateRoleRequest $request, Role $role): array
    {
        return $this->service->updateRole($role, $request->validated());
    }

    /**
     * Delete role.
     *
     * @param DeleteRoleRequest $request
     * @param Role $role
     * @return array
     */
    public function deleteRole(DeleteRoleRequest $request, Role $role): array
    {
        return $this->service->deleteRole($role);
    }
}
