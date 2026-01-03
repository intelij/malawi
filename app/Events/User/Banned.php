<?php

namespace App\Events\User;

use App\Models\User;

class Banned
{
    public function __construct(protected User $bannedUser)
    {
    }

    public function getBannedUser(): User
    {
        return $this->bannedUser;
    }
}
