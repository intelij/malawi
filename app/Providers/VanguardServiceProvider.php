<?php

namespace App\Providers;

use Vanguard\Plugins\VanguardServiceProvider as BaseVanguardServiceProvider;
use App\Support\Plugins\Dashboard\Widgets\BannedUsers;
use App\Support\Plugins\Dashboard\Widgets\LatestRegistrations;
use App\Support\Plugins\Dashboard\Widgets\NewUsers;
use App\Support\Plugins\Dashboard\Widgets\RegistrationHistory;
use App\Support\Plugins\Dashboard\Widgets\TotalUsers;
use App\Support\Plugins\Dashboard\Widgets\UnconfirmedUsers;
use App\Support\Plugins\Dashboard\Widgets\UserActions;
use App\Support\Plugins\Dashboard\Widgets\UsersAwaitingApproval;
use Vanguard\UserActivity\Widgets\ActivityWidget;

class VanguardServiceProvider extends BaseVanguardServiceProvider
{
    /**
     * List of registered plugins.
     */
    protected function plugins(): array
    {
        return [
            \App\Support\Plugins\Dashboard\Dashboard::class,
            \App\Support\Plugins\Users::class,
            \Vanguard\UserActivity\UserActivity::class,
            \App\Support\Plugins\RolesAndPermissions::class,
            \App\Support\Plugins\Settings::class,
            \Vanguard\Announcements\Announcements::class,
        ];
    }

    /**
     * Dashboard widgets.
     */
    protected function widgets(): array
    {
        return [
            UsersAwaitingApproval::class,
            UserActions::class,
            TotalUsers::class,
            NewUsers::class,
            BannedUsers::class,
            UnconfirmedUsers::class,
            RegistrationHistory::class,
            LatestRegistrations::class,
            ActivityWidget::class,
        ];
    }
}
