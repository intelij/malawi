<?php

namespace App\Events\Role;

use App\Models\Role;

abstract class RoleEvent
{
    public function __construct(protected Role $role)
    {
    }

    public function getRole(): Role
    {
        return $this->role;
    }
}
