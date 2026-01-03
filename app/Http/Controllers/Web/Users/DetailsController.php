<?php

namespace App\Http\Controllers\Web\Users;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Events\User\Approved;
use App\Events\User\Banned;
use App\Events\User\UpdatedByAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateDetailsRequest;
use App\Models\User;
use App\Repositories\User\UserRepository;
use App\Support\Enum\UserStatus;

class DetailsController extends Controller
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function update(User $user, UpdateDetailsRequest $request): RedirectResponse
    {
        $data = $request->all();

        if (! data_get($data, 'country_id')) {
            $data['country_id'] = null;
        }

        $this->users->update($user->id, $data);
        $this->users->setRole($user->id, $request->role_id);

        event(new UpdatedByAdmin($user));

        // If user status was updated to "Banned",
        // fire the appropriate event.
        if ($this->userWasBanned($user, $request)) {
            event(new Banned($user));
        }

        if ($this->userWasApproved($user, $request)) {
            event(new Approved($user));
        }

        return redirect()->back()
            ->withSuccess(__('User updated successfully.'));
    }

    public function approve(User $user): RedirectResponse
    {
        $this->users->update($user->id, [
            'status' => UserStatus::ACTIVE
        ]);

        event(new UpdatedByAdmin($user));
        event(new Approved($user));

        return redirect()->back()
            ->withSuccess(__('User updated successfully.'));
    }

    /**
     * Check if user is banned during last update.
     */
    private function userWasBanned(User $user, Request $request): bool
    {
        return $user->status != $request->status
            && $request->status == UserStatus::BANNED->value;
    }

    /**
     * Check if user is approved during last update.
     */
    private function userWasApproved(User $user, Request $request): bool
    {
        return $user->status == UserStatus::WAITING_APPROVAL->value
            && $request->status == UserStatus::ACTIVE->value;
    }
}
