<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class UpdateDetailsRequest extends Request
{
    public function rules(): array
    {
        return [
            'birthday' => 'nullable|date',
            'role_id' => 'required|exists:roles,id',
        ];
    }
}
