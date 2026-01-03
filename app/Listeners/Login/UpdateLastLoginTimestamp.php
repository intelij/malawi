<?php

namespace App\Listeners\Login;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;
use App\Events\User\LoggedIn;
use App\Repositories\User\UserRepository;

class UpdateLastLoginTimestamp
{
    public function __construct(private readonly UserRepository $users, private readonly Guard $guard)
    {
    }

    public function handle(LoggedIn $event): void
    {
        $this->users->update(
            $this->guard->id(),
            ['last_login' => Carbon::now()]
        );
    }
}
