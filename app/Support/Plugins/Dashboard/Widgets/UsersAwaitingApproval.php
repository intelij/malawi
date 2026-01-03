<?php

namespace App\Support\Plugins\Dashboard\Widgets;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Vanguard\Plugins\Widget;
use App\Repositories\User\UserRepository;
use App\Support\Enum\UserStatus;

class UsersAwaitingApproval extends Widget
{
    /**
     * {@inheritdoc}
     */
    protected string|\Closure|array $permissions = 'users.manage';

    public ?string $width = '12';

    public function __construct(protected readonly UserRepository $users)
    {
    }

    public function authorize(Authenticatable $user): bool
    {
        if (!setting('approval.enabled')) {
            return false;
        }

        return parent::authorize($user);
    }

    /**
     * {@inheritDoc}
     */
    public function render(): View
    {
        return view('plugins.dashboard.widgets.users-awaiting-approval', [
            'count' => $this->users->countByStatus(UserStatus::WAITING_APPROVAL),
        ]);
    }
}
