<?php

namespace App\Listeners\Approve;

use Illuminate\Auth\Events\Verified;
use Mail;
use App\Models\Role;
use App\Repositories\User\UserRepository;

class SendWaitingApprovalNotification
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function handle(Verified $event): void
    {
        if (!setting('approval.enabled')) {
            return;
        }

        foreach ($this->users->getUsersWithRole(Role::DEFAULT_ADMIN_ROLE) as $user) {
            Mail::to($user)->send(new \Vanguard\Mail\WaitingApproval($event->user));
        }
    }
}
