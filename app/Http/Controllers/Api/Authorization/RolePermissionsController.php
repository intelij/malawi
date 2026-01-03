<?php

namespace App\Http\Controllers\Api\Authorization;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Events\Role\PermissionsUpdated;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Role\UpdateRolePermissionsRequest;
use App\Http\Resources\PermissionResource;
use App\Models\Role;
use App\Repositories\Role\RoleRepository;

class RolePermissionsController extends ApiController
{
    public function __construct(private RoleRepository $roles)
    {
        $this->middleware('permission:permissions.manage');
    }

    public function show(Role $role): AnonymousResourceCollection
    {
        return PermissionResource::collection($role->cachedPermissions());
    }

    public function update(Role $role, UpdateRolePermissionsRequest $request): AnonymousResourceCollection
    {
        $this->roles->updatePermissions(
            roleId: $role->id,
            permissions: $request->permissions
        );

        event(new PermissionsUpdated);

        return PermissionResource::collection($role->cachedPermissions());
    }
}
