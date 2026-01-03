<?php

namespace App\Events\User;

use App\Models\User;

class UpdatedByAdmin
{
    public function __construct(protected User $updatedUser)
    {
    }

    public function getUpdatedUser(): User
    {
        return $this->updatedUser;
    }
}
