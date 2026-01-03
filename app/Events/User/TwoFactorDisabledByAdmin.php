<?php

namespace App\Events\User;

use App\Models\User;

class TwoFactorDisabledByAdmin
{
    public function __construct(protected User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
