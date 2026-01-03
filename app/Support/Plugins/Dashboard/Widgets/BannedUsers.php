<?php

namespace App\Support\Plugins\Dashboard\Widgets;

use Illuminate\Contracts\View\View;
use Vanguard\Plugins\Widget;
use App\Repositories\User\UserRepository;
use App\Support\Enum\UserStatus;

class BannedUsers extends Widget
{
    /**
     * {@inheritdoc}
     */
    public ?string $width = '3';

    /**
     * {@inheritdoc}
     */
    protected string|\Closure|array $permissions = 'users.manage';

    public function __construct(protected readonly UserRepository $users)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function render(): View
    {
        return view('plugins.dashboard.widgets.banned-users', [
            'count' => $this->users->countByStatus(UserStatus::BANNED),
        ]);
    }
}
