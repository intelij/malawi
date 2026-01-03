<?php

namespace App\Http\Requests\Permission;

use App\Http\Requests\Request;

class RemovePermissionRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->route('permission')->removable;
    }

    public function rules(): array
    {
        return [];
    }
}
