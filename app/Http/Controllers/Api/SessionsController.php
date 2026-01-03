<?php

namespace App\Http\Controllers\Api;

use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Resources\SessionResource;
use App\Repositories\Session\SessionRepository;

class SessionsController extends ApiController
{
    public function __construct(private readonly SessionRepository $sessions)
    {
        $this->middleware('session.database');
    }

    /**
     * @throws AuthorizationException
     */
    public function show($session): SessionResource
    {
        $this->authorize('manage-session', $session);

        return new SessionResource($session);
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy($session): \Illuminate\Http\JsonResponse
    {
        $this->authorize('manage-session', $session);

        $this->sessions->invalidateSession($session->id);

        return $this->respondWithSuccess();
    }
}
