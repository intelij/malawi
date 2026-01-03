<?php

namespace App\Http\Requests\User;

use App\Models\User;

class UpdateProfileLoginDetailsRequest extends UpdateLoginDetailsRequest
{
    protected function getUserForUpdate(): User
    {
        return \Auth::user();
    }
}
