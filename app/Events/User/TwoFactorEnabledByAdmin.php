<?php

namespace App\Events\User;

use App\Models\User;

class TwoFactorEnabledByAdmin
{
    public function __construct(protected User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
