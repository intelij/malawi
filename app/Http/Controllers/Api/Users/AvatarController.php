<?php

namespace App\Http\Controllers\Api\Users;

use Illuminate\Http\Request;
use App\Events\User\UpdatedByAdmin;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\User\UploadAvatarRawRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\User\UserRepository;
use App\Services\Upload\UserAvatarManager;

class AvatarController extends ApiController
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly UserAvatarManager $avatarManager
    ) {
        $this->middleware('permission:users.manage');
    }

    public function update(User $user, UploadAvatarRawRequest $request): UserResource
    {
        $name = $this->avatarManager->uploadAndCropAvatar($request->file('file'));

        $user = $this->users->update($user->id, ['avatar' => $name]);

        event(new UpdatedByAdmin($user));

        return new UserResource($user);
    }

    public function updateExternal(User $user, Request $request): UserResource
    {
        $this->validate($request, ['url' => 'required|url']);

        $this->avatarManager->deleteAvatarIfUploaded($user);

        $user = $this->users->update($user->id, ['avatar' => $request->url]);

        event(new UpdatedByAdmin($user));

        return new UserResource($user);
    }

    /**
     * Remove user's avatar and set it to null.
     */
    public function destroy(User $user): UserResource
    {
        $this->avatarManager->deleteAvatarIfUploaded($user);

        $user = $this->users->update($user->id, ['avatar' => null]);

        event(new UpdatedByAdmin($user));

        return new UserResource($user);
    }
}
