<?php

namespace App\Http\Controllers\Web\Users;

use Illuminate\Http\RedirectResponse;
use App\Events\User\UpdatedByAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateLoginDetailsRequest;
use App\Models\User;
use App\Repositories\User\UserRepository;

class LoginDetailsController extends Controller
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function update(User $user, UpdateLoginDetailsRequest $request): RedirectResponse
    {
        $data = $request->all();
        $data['force_password_change'] = $request->filled('force_password_change');

        if (! $data['password']) {
            unset($data['password']);
            unset($data['password_confirmation']);
        }

        $this->users->update($user->id, $data);

        event(new UpdatedByAdmin($user));

        return redirect()->route('users.edit', $user->id)
            ->withSuccess(__('Login details updated successfully.'));
    }
}
