<?php

namespace App\Events\Permission;

use App\Models\Permission;

abstract class PermissionEvent
{
    public function __construct(protected Permission $permission)
    {
    }

    public function getPermission(): Permission
    {
        return $this->permission;
    }
}
