<?php

namespace App\Events\User;

use App\Models\User;

class Created
{
    public function __construct(protected User $createdUser)
    {
    }

    public function getCreatedUser(): User
    {
        return $this->createdUser;
    }
}
