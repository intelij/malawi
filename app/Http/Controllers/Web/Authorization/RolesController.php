<?php

namespace App\Http\Controllers\Web\Authorization;

use Cache;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\CreateRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Role;
use App\Repositories\Role\RoleRepository;
use App\Repositories\User\UserRepository;

class RolesController extends Controller
{
    public function __construct(private readonly RoleRepository $roles)
    {
    }

    public function index(): View
    {
        return view('role.index', ['roles' => $this->roles->getAllWithUsersCount()]);
    }

    public function create(): View
    {
        return view('role.add-edit', ['edit' => false]);
    }

    public function store(CreateRoleRequest $request): RedirectResponse
    {
        $this->roles->create($request->all());

        return redirect()->route('roles.index')
            ->withSuccess(__('Role created successfully.'));
    }

    public function edit(Role $role): View
    {
        return view('role.add-edit', [
            'role' => $role,
            'edit' => true,
        ]);
    }

    public function update(Role $role, UpdateRoleRequest $request): RedirectResponse
    {
        $this->roles->update($role->id, $request->all());

        return redirect()->route('roles.index')
            ->withSuccess(__('Role updated successfully.'));
    }

    public function destroy(Role $role, UserRepository $userRepository): RedirectResponse
    {
        if (! $role->removable) {
            throw new NotFoundHttpException;
        }

        $userRole = $this->roles->findByName(Role::DEFAULT_USER_ROLE);

        $userRepository->switchRolesForUsers($role->id, $userRole->id);

        $this->roles->delete($role->id);

        Cache::flush();

        return redirect()->route('roles.index')
            ->withSuccess(__('Role deleted successfully.'));
    }
}
