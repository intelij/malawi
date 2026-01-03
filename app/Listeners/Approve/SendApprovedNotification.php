<?php

namespace App\Listeners\Approve;

use Mail;
use App\Events\User\Approved;

class SendApprovedNotification
{
    public function __construct()
    {
    }

    public function handle(Approved $event): void
    {
        if (! setting('approval.enabled')) {
            return;
        }

        Mail::to($event->getApprovedUser())->send(new \Vanguard\Mail\UserApproved());
    }
}
