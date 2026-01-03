<?php

namespace App\Events\User;

use App\Models\User;

class Deleted
{
    public function __construct(protected User $deletedUser)
    {
    }

    public function getDeletedUser(): User
    {
        return $this->deletedUser;
    }
}
