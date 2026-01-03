<?php

namespace App\Http\Controllers\Web\Profile;

use Cache;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateProfileLoginDetailsRequest;
use App\Repositories\User\UserRepository;
use App\Support\Authorization\PasswordChangeManager;

class LoginDetailsController extends Controller
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function update(UpdateProfileLoginDetailsRequest $request, PasswordChangeManager $passwordChangeManager): RedirectResponse
    {
        $data = $request->except('role', 'status');

        // If password is not provided, then we will
        // just remove it from $data array and do not change it
        if (! data_get($data, 'password')) {
            unset($data['password']);
            unset($data['password_confirmation']);
        } else {
            $data['force_password_change'] = false;
            $passwordChangeManager->liftPasswordChangeRequest(auth()->user());
        }

        $this->users->update(auth()->id(), $data);

        return redirect()->route('profile')
            ->withSuccess(__('Login details updated successfully.'));
    }
}
