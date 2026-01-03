<?php

namespace App\Http\Controllers\Api\Profile;

use App\Events\User\UpdatedProfileDetails;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\User\UpdateProfileDetailsRequest;
use App\Http\Resources\UserResource;
use App\Repositories\User\UserRepository;

class DetailsController extends ApiController
{
    public function index(): UserResource
    {
        return new UserResource(auth()->user());
    }

    public function update(UpdateProfileDetailsRequest $request, UserRepository $users): UserResource
    {
        $user = $request->user();

        $data = collect($request->all());

        $data = $data->only([
            'first_name', 'last_name', 'birthday',
            'phone', 'address', 'country_id',
        ])->toArray();

        if (! isset($data['country_id'])) {
            $data['country_id'] = $user->country_id;
        }

        $user = $users->update($user->id, $data);

        event(new UpdatedProfileDetails);

        return new UserResource($user);
    }
}
