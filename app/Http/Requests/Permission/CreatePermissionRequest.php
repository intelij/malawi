<?php

namespace App\Http\Requests\Permission;

use Illuminate\Validation\Rule;
use App\Rules\ValidPermissionName;

class CreatePermissionRequest extends BasePermissionRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                new ValidPermissionName,
                Rule::unique('permissions', 'name'),
            ],
        ];
    }
}
