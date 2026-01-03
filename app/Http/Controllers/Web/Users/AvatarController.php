<?php

namespace App\Http\Controllers\Web\Users;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Events\User\UpdatedByAdmin;
use App\Http\Controllers\Api\ApiController;
use App\Models\User;
use App\Repositories\User\UserRepository;
use App\Services\Upload\UserAvatarManager;

class AvatarController extends ApiController
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly UserAvatarManager $avatarManager
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function update(User $user, Request $request): RedirectResponse
    {
        $this->validate($request, ['avatar' => 'image']);

        $name = $this->avatarManager->uploadAndCropAvatar(
            $request->file('avatar'),
            $request->get('points')
        );

        if ($name) {
            $this->users->update($user->id, ['avatar' => $name]);

            event(new UpdatedByAdmin($user));

            return redirect()->route('users.edit', $user)
                ->withSuccess(__('Avatar changed successfully.'));
        }

        return redirect()->route('users.edit', $user)
            ->withErrors(__('Avatar image cannot be updated. Please try again.'));
    }

    /**
     * Update user's avatar from some external source (Gravatar, Facebook, Twitter...)
     */
    public function updateExternal(User $user, Request $request): RedirectResponse
    {
        $this->avatarManager->deleteAvatarIfUploaded($user);

        $this->users->update($user->id, ['avatar' => $request->get('url')]);

        event(new UpdatedByAdmin($user));

        return redirect()->route('users.edit', $user)
            ->withSuccess(__('Avatar changed successfully.'));
    }
}
