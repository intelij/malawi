<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Support\MembershipNumberGenerator;

class AssignMembershipNumber
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (is_null($user->membership_number)) {
            $user->update([
                'membership_number' => MembershipNumberGenerator::generate(),
            ]);
        }
    }
}
