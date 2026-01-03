<?php

namespace App\Listeners\Users;

use Illuminate\Auth\Events\Verified;
use App\Repositories\User\UserRepository;
use App\Support\Enum\UserStatus;

class ActivateUser
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function handle(Verified $event): void
    {
        $this->users->update($event->user->id, [
            'status' => setting('approval.enabled') ? UserStatus::WAITING_APPROVAL : UserStatus::ACTIVE,
        ]);
    }
}
