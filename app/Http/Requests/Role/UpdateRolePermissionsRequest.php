<?php

namespace App\Http\Requests\Role;

use Illuminate\Validation\Rule;
use App\Http\Requests\Request;
use App\Models\Permission;

class UpdateRolePermissionsRequest extends Request
{
    public function rules(): array
    {
        $permissions = Permission::pluck('id')->toArray();

        return [
            'permissions' => 'required|array',
            'permissions.*' => Rule::in($permissions),
        ];
    }

    public function messages(): array
    {
        return [
            'permissions.*' => 'Provided permission does not exist.',
        ];
    }
}
