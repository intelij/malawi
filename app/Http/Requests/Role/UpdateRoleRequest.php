<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\Request;

class UpdateRoleRequest extends Request
{
    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => 'required|regex:/^[a-zA-Z0-9\-_\.]+$/|unique:roles,name,'.$role->id,
        ];
    }
}
