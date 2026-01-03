<?php

namespace App\Http\Controllers\Api\Users;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\SessionResource;
use App\Models\User;
use App\Repositories\Session\SessionRepository;

class SessionsController extends ApiController
{
    public function __construct()
    {
        $this->middleware('permission:users.manage');
        $this->middleware('session.database');
    }

    public function index(User $user, SessionRepository $sessions): AnonymousResourceCollection
    {
        return SessionResource::collection(
            $sessions->getUserSessions($user->id)
        );
    }
}
