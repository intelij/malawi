<?php

namespace App\Support\Plugins;

use Vanguard\Plugins\Plugin;
use App\Support\Sidebar\Item;

class Users extends Plugin
{
    public function sidebar(): Item
    {
        return Item::create(__('Users'))
            ->route('users.index')
            ->icon('fas fa-users')
            ->active('users*')
            ->permissions('users.manage');
    }
}
