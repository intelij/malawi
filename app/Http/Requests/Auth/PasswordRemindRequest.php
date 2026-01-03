<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class PasswordRemindRequest extends Request
{
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
        ];
    }
}
