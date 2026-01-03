<?php

namespace App\Http\Controllers\Web\Authorization;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\CreatePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Models\Permission;
use App\Repositories\Permission\PermissionRepository;
use App\Repositories\Role\RoleRepository;

class PermissionsController extends Controller
{
    public function __construct(
        private readonly RoleRepository $roles,
        private readonly PermissionRepository $permissions
    ) {
    }

    public function index(): View
    {
        return view('permission.index', [
            'roles' => $this->roles->all(),
            'permissions' => $this->permissions->all(),
        ]);
    }

    public function create(): View
    {
        return view('permission.add-edit', ['edit' => false]);
    }

    public function store(CreatePermissionRequest $request): RedirectResponse
    {
        $this->permissions->create($request->all());

        return redirect()->route('permissions.index')
            ->withSuccess(__('Permission created successfully.'));
    }

    public function edit(Permission $permission): View
    {
        return view('permission.add-edit', [
            'edit' => true,
            'permission' => $permission,
        ]);
    }

    public function update(Permission $permission, UpdatePermissionRequest $request): RedirectResponse
    {
        $this->permissions->update($permission->id, $request->all());

        return redirect()->route('permissions.index')
            ->withSuccess(__('Permission updated successfully.'));
    }

    /**
     * @throws NotFoundHttpException
     */
    public function destroy(Permission $permission)
    {
        if (! $permission->removable) {
            throw new NotFoundHttpException;
        }

        $this->permissions->delete($permission->id);

        return redirect()->route('permissions.index')
            ->withSuccess(__('Permission deleted successfully.'));
    }
}
