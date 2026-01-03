<?php

namespace App\Http\Controllers\Api\Profile;

use Illuminate\Http\Request;
use App\Events\User\ChangedAvatar;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\User\UploadAvatarRawRequest;
use App\Http\Resources\UserResource;
use App\Repositories\User\UserRepository;
use App\Services\Upload\UserAvatarManager;

class AvatarController extends ApiController
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly UserAvatarManager $avatarManager
    ) {
    }

    public function update(UploadAvatarRawRequest $request): UserResource
    {
        $name = $this->avatarManager->uploadAndCropAvatar(
            $request->file('file')
        );

        $user = $this->users->update(
            auth()->id(),
            ['avatar' => $name]
        );

        event(new ChangedAvatar);

        return new UserResource($user);
    }

    public function updateExternal(Request $request): UserResource
    {
        $this->validate($request, [
            'url' => 'required|url',
        ]);

        $this->avatarManager->deleteAvatarIfUploaded(
            auth()->user()
        );

        $user = $this->users->update(
            auth()->id(),
            ['avatar' => $request->url]
        );

        event(new ChangedAvatar);

        return new UserResource($user);
    }

    public function destroy(): UserResource
    {
        $user = auth()->user();

        $this->avatarManager->deleteAvatarIfUploaded($user);

        $user = $this->users->update(
            $user->id,
            ['avatar' => null]
        );

        event(new ChangedAvatar);

        return new UserResource($user);
    }
}
