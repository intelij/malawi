<?php

namespace App\Listeners\Users;

use App\Events\User\Banned;
use App\Repositories\Session\SessionRepository;

class InvalidateSessions
{
    public function __construct(private readonly SessionRepository $sessions)
    {
    }

    public function handle(Banned $event): void
    {
        $user = $event->getBannedUser();

        $this->sessions->invalidateAllSessionsForUser($user->id);
    }
}
