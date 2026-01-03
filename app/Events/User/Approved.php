<?php

namespace App\Events\User;

use App\Models\User;

class Approved
{
    public function __construct(protected User $approvedUser)
    {
    }

    public function getApprovedUser(): User
    {
        return $this->approvedUser;
    }
}
