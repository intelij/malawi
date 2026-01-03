<?php

namespace App\Http\Requests\Permission;

use Illuminate\Validation\Rule;
use App\Rules\ValidPermissionName;

class UpdatePermissionRequest extends BasePermissionRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                new ValidPermissionName,
                Rule::unique('permissions', 'name')->ignore($this->route('permission')->id),
            ],
        ];
    }
}
