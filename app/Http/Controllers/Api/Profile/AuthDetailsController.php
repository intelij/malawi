<?php

namespace App\Http\Controllers\Api\Profile;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\User\UpdateProfileLoginDetailsRequest;
use App\Http\Resources\UserResource;
use App\Repositories\User\UserRepository;

class AuthDetailsController extends ApiController
{
    public function update(UpdateProfileLoginDetailsRequest $request, UserRepository $users): UserResource
    {
        $user = $request->user();

        $data = $request->only(['email', 'username', 'password']);

        $user = $users->update($user->id, $data);

        return new UserResource($user);
    }
}
