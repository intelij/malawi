<?php

namespace App\Http\Controllers\Api\Auth\Password;

use Illuminate\Http\JsonResponse;
use Password;
use App\Events\User\RequestedPasswordResetEmail;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Auth\PasswordRemindRequest;
use App\Mail\ResetPassword;
use App\Repositories\User\UserRepository;

class RemindController extends ApiController
{
    /**
     * Send a reset link to the given user.
     */
    public function index(PasswordRemindRequest $request, UserRepository $users): JsonResponse
    {
        $user = $users->findByEmail($request->email);

        $token = Password::getRepository()->create($user);

        \Mail::to($user)->send(new ResetPassword($token));

        event(new RequestedPasswordResetEmail($user));

        return $this->respondWithSuccess();
    }
}
