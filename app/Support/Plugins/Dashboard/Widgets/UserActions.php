<?php

namespace App\Support\Plugins\Dashboard\Widgets;

use Illuminate\Contracts\View\View;
use App\Models\User;
use Vanguard\Plugins\Widget;

class UserActions extends Widget
{
    public function __construct()
    {
        $this->permissions(function (User $user) {
            return $user->hasRole('User');
        });
    }

    /**
     * {@inheritdoc}
     */
    public function render(): View
    {
        return view('plugins.dashboard.widgets.user-actions');
    }
}
