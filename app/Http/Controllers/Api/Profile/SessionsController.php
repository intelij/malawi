<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\SessionResource;
use App\Repositories\Session\SessionRepository;

class SessionsController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('session.database');
    }

    public function index(SessionRepository $sessions): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $sessions = $sessions->getUserSessions(auth()->id());

        return SessionResource::collection($sessions);
    }
}
